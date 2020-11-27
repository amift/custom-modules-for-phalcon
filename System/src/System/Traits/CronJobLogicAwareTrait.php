<?php

namespace System\Traits;

use System\Tool\CronJobState as State;

trait CronJobLogicAwareTrait
{

    /**
     * Check if is finished
     * 
     * @return boolean 
     */
    public function isStateFinished()
    {
        return $this->state === State::STATUS_SUCCESS ? true : false;
    }

    /**
     * Check if is running
     * 
     * @return boolean 
     */
    public function isStateRunning()
    {
        return $this->state === State::STATUS_RUNNING ? true : false;
    }

    /**
     * Check if is error
     * 
     * @return boolean 
     */
    public function isStateError()
    {
        return $this->state === State::STATUS_ERROR ? true : false;
    }

    public function startRunning()
    {
        $this->setState(State::STATUS_RUNNING);
        $this->setLaunchedAt(new \DateTime('now'));
    }

    public function stopRunning($stackTrace = '', $success = true, $errorMsg = '')
    {
        if (!$success) {
            $this->setState(State::STATUS_ERROR);
        } else {
            $this->setState(State::STATUS_SUCCESS);
        }
        $this->setFinishedAt(new \DateTime('now'));

        $log = new \System\Entity\CronJobLog();
        $log->setErrorMsg($errorMsg);
        $log->setStackTrace($stackTrace);
        $log->setLaunchedAt($this->getLaunchedAt());
        $log->setFinishedAt($this->getFinishedAt());
        $this->addLog($log);
    }

}
