# redisDelayQueue
laravel redis delay queue

本包使用redis 完成laravel 延迟队列
使用redis lua脚本保证原子性

安装方法:
composer require firstmeet/redis-delay-queue



安装完成之后执行:
将  Firstmeet\RedisDelayQueue\RedisDelayQueueProvider 添加到config/app.php中
php artian config:clear
php artisan vendor:publish 然乎选择  Firstmeet\RedisDelayQueue\RedisDelayQueueProvider

监听队列的命令:
php artisan redis-delay

使用方法示例:
app('redisDelayQueue')->addQueue(function(){
 echo 1;
},100)

加入队列的方法 addQueue
第一个参数是匿名函数,第二个参数是延迟多少秒,如果是0，就是立即执行

使用Facade:
\Firstmeet\RedisDelayQueue\Facades\RedisDelayQueueFacade::addQueue(function (){
        echo 1;
    },10);
