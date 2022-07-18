<?php

namespace Song\RedisDelayQueue\Facades;

class RedisDelayQueueFacade extends \Illuminate\Support\Facades\Facade
{
   protected static function getFacadeAccessor():string
   {
      return 'redisDelayQueue';
   }
}
