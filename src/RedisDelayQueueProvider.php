<?php

namespace Song\RedisDelayQueue;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Predis\PubSub\Consumer;

class RedisDelayQueueProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register():void
    {
      $this->app->bind('redisDelayQueue',function ($app){
          return new RedisDelayQueue($app);
      });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot():void
    {
        $this->publishes([__DIR__.'/config/delay.php'=>config_path('delay.php')]);
        $app=$this->app;
        Artisan::command('redis-delay',function ()use ($app){
            $queue=new RedisDelayQueue($app);
            $queue->Consumer();

        });
    }
    public function provides()
    {
        return ['redisDelayQueue'] ; // TODO: Change the autogenerated stub
    }
}