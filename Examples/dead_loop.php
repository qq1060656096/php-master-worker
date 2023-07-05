<?php
include dirname(__DIR__).'/vendor/autoload.php';

// 一次最多执行3个任务
$maxProcessCount = 3;
$masterWorker = new \Zwei\MasterWorker\MasterWorker($maxProcessCount);
$count = 0;
while(true) {
    $count ++;
    $masterWorker->addTask($count.' task1', 'php -r "sleep(20); echo \''.$count.':task1.20\';"');
    $masterWorker->addTask($count.' task2', 'php -r "sleep(8); echo \''.$count.':task2.8\';"');
    $masterWorker->addTask($count.' task3', 'php -r "sleep(13); echo \''.$count.':task3.13\';"');
    $masterWorker->addTask($count.' task4', 'php -r "sleep(15); echo \''.$count.':task4.15\';"');
    $masterWorker->addTask($count.' task5', 'php -r "sleep(18); echo \''.$count.':task5.18\';"');
    $masterWorker->run();
}