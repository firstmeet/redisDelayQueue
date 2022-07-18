<?php

namespace Firstmeet\RedisDelayQueue\Command;

use App\Services\RedisDelayQueue;

class RedisDelayConsumer extends \Illuminate\Console\Command
{
    public string $redis_delay_key = "redis_delay";
    public string $redis_delay_map="redis_delay_map";
    public string $redis_reverse_key;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis-consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app('redisDelayQueue')->Consumer();
    }
}
