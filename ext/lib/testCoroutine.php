<?php
class J7SchedulerTask {
	protected $taskId;
	protected $coroutine;
	protected $sendValue = null;
	protected $beforeFirstYield = true;

	public function __construct($taskId, Generator $coroutine) {
		$this->taskId = $taskId;
		$this->coroutine = $coroutine;
	}

	public function getTaskId() {
		return $this->taskId;
	}

	public function setSendValue($sendValue) {
		$this->sendValue = $sendValue;
	}

	public function run() {
		if ($this->beforeFirstYield) {
			$this->beforeFirstYield = false;
			return $this->coroutine->current();
		} else {
			$retval = $this->coroutine->send($this->sendValue);
			$this->sendValue = null;
			return $retval;
		}
	}

	public function isFinished() {
		return !$this->coroutine->valid();
	}
}


class J7SchedulerCall {
	protected $callback;

	public function __construct(callable $callback) {
		$this->callback = $callback;
	}

	public function __invoke(J7SchedulerTask $task, J7Scheduler $scheduler) {
		$callback = $this->callback;
		return $callback($task, $scheduler);
	}
}

class J7Scheduler {
	protected $maxTaskId = 0;
	protected $taskMap = []; // taskId => task
	protected $taskQueue;

	public function __construct() {
		$this->taskQueue = new SplQueue();
	}

	public function newTask(Generator $coroutine) {
		$tid = ++$this->maxTaskId;
		$task = new J7SchedulerTask($tid, $coroutine);
		$this->taskMap[$tid] = $task;
		$this->schedule($task);
		return $tid;
	}

	public function schedule(J7SchedulerTask $task) {
		$this->taskQueue->enqueue($task);
	}
    
    public function killTask($tid) {
        if (!isset($this->taskMap[$tid])) {
            return false;
        }

        unset($this->taskMap[$tid]);

        // This is a bit ugly and could be optimized so it does not have to walk the queue,
        // but assuming that killing tasks is rather rare I won't bother with it now
        foreach ($this->taskQueue as $i => $task) {
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }

        return true;
    }

	public function run() {
		while (!$this->taskQueue->isEmpty()) {
			$task = $this->taskQueue->dequeue();
			$retval = $task->run();

			if ($retval instanceof J7SchedulerCall) {
				$retval($task, $this);  //传递给 J7SchedulerCall __invoke
				continue;
			}

			if ($task->isFinished()) {
				unset($this->taskMap[$task->getTaskId()]);
			} else {
				$this->schedule($task);
			}
		}
	}
}