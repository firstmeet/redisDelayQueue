<?php

namespace Song\RedisDelayQueue;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisDelayQueue
{
    public int $delay = 0;
    public string $redis_delay_key = "redis_delay";
    public string $redis_delay_map="redis_delay_map";
    public string $redis_reverse_key;

    public function __construct(Application $app)
    {
        $this->redis_delay_key=app('config')->get('delay.redis_delay_key');
        $this->redis_reverse_key=app('config')->get('delay.redis_reverse_key');
        $this->redis_delay_map=app('config')->get('delay.redis_delay_map');
    }

    public function addQueue(\Closure $queue, int $delay=0): void
    {
        if (!$delay) {
            $delay = $this->delay;
        }
        $uuid=\Ramsey\Uuid\Uuid::uuid1()->toString();

        $serialized=serialize(new \Laravel\SerializableClosure\SerializableClosure($queue));
        $push_queue=json_encode($serialized);
        Redis::eval(<<<'LUA'
        redis.call('hmset',KEYS[1],ARGV[1],ARGV[2])
        redis.call('zadd',KEYS[2],ARGV[3],ARGV[1])
LUA,2,$this->redis_delay_map,$this->redis_delay_key,$uuid,$push_queue,Carbon::now()->addSeconds($delay)->timestamp);
    }
    public function Consumer():void
    {

        while (true){
            $result = Redis::eval(<<<'LUA'
    local result=redis.call('zrangebyscore',KEYS[1],'-inf',ARGV[1],'limit',0,1)
    if result[1] then
       redis.call('zrem',KEYS[1],result[1])
       local val=redis.call('hget',KEYS[3],result[1])
       redis.call('lpush',KEYS[2],val)
       redis.call('hdel',KEYS[3],result[1])
       return result[1]
    end
    return false
LUA, 3, $this->redis_delay_key,$this->redis_reverse_key,$this->redis_delay_map,Carbon::now()->timestamp);
            if ($result){
                echo $result." start\n";
                $lpop = Redis::lpop($this->redis_reverse_key);
                $result_queue=json_decode($lpop);
                $fn=unserialize($result_queue)->getClosure();
                try {
                    $fn();
                    echo $result." finished\n";
//                    $re=Redis::eval(<<<'LUA'
//              redis.call('hdel',KEYS[1],ARGV[1])
//              return 1
//LUA,1,$this->redis_delay_map,$result);
//                    Log::info('re',[$re]);
                }catch (\Exception $exception){
                    Log::info('queue exception',[$exception->getMessage()]);
                    $this->failed($result,$lpop);
                }

            }else{
                sleep(1);
            }
//           $zrange=Redis::zrangebyscore($queue->redis_delay_key,'-inf',Carbon::now()->timestamp,['limit'=>[0,1]]);
//           if (!empty($zrange)){
//               $current_queue=$zrange[0];
//               Redis::zrem($queue->redis_delay_key,$current_queue);
//               $result=json_decode($current_queue);
//               $fn=unserialize($result)->getClosure();
//               $fn();
//           }
        }

    }
    public function failed($uuid,$push_queue,$delay=0):void
    {
        Redis::eval(<<<'LUA'
        redis.call('hmset',KEYS[1],ARGV[1],ARGV[2])
        redis.call('zadd',KEYS[2],ARGV[3],ARGV[1])
LUA,2,$this->redis_delay_map,$this->redis_delay_key,$uuid,$push_queue,Carbon::now()->addSeconds($delay)->timestamp);
    }

}
