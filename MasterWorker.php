<?php
namespace Zwei\MasterWorker;

use Symfony\Component\Process\Process;

class MasterWorker {

    /**
     * @var int 最大运行进程数
     */
    protected $maxProcessCount = 0;
    
    /**
     * @var null|integer 多少秒超时，null禁用超时
     */
    protected $timeout = null;
    /**
     * @var array [taskId => command string]
     */
    protected $tasks = [];

    /**
     * @var array [taskId => Process]
     */
    protected $workers = [];

    /**
     * @var static
     */
    protected $failedMasterWorker = null;


    public function __construct($maxProcessCount, $timeout = null)
    {
        $this->setMaxProcessCount($maxProcessCount);
        $this->setTimeout($timeout);
    }

    /**
     * @return int
     */
    public function getMaxProcessCount()
    {
        return $this->maxProcessCount;
    }

    /**
     * @param int $maxProcessCount
     */
    protected function setMaxProcessCount($maxProcessCount)
    {
        $this->maxProcessCount = $maxProcessCount;
    }


    /**
     * @param integer $taskId
     * @param string $command
     * @return void
     */
    public function addTask($taskId, $command) {
        $this->tasks[$taskId] = $command;
    }

    /**
     * 多少秒超时，null禁用超时
     *
     * @return integer|null
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * 多少秒超时，null禁用超时
     *
     * @param null|integer $timeout
     */
    protected function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }


    /**
     * @param string $taskId
     * @param Process $worker
     * @return void
     */
    protected function addWorker($taskId, Process $worker) {
        $this->workers[$taskId] = $worker;
    }

    // 1. 子进程列表
    // 2. 开启子进程
    // 3. 等待子进程结束

    public function start() {
        foreach ($this->tasks as $taskId => $command) {
            if ($this->checkMaxRunProcessCount()) {
                continue;
            }
            unset($this->tasks[$taskId]);
            $worker = new Process($command);
            $worker->setTimeout($this->getTimeout());
            $worker->start();
            $this->addWorker($taskId, $worker);
        }
    }

    /**
     * @return int 获取运行中的进程数
     */
    public function getRunProcessCount() {
        return count($this->workers);
    }

    /**
     * @return array<string,TaskResult>
     */
    public function check() {
        $results = [];
        /* @var Process $worker */
        foreach ($this->workers as $taskId => $worker) {
            if (!$worker->isRunning()) {  // 进程已经完成
                unset($this->workers[$taskId]);
                continue;
            }
        }
    }

    // 编写一个函数，用于检查是否超过最大进程数，返回 bool 值



    /**
     * @return bool 是否超过最大进程数
     */
    public function checkMaxRunProcessCount() {
        return $this->getRunProcessCount() >= $this->getMaxProcessCount();
    }

    /**
     * 可以多次调用， 超过最大进程数，会等待
     * 小于最大进程数，会启动队列中的进程
     *
     *
     */
    public function run() {
        while(true) {
            $this->start();
            $this->check();
            if ($this->checkMaxRunProcessCount()) {
                sleep(1);
                continue;
            }
            break;
        }
    }

}