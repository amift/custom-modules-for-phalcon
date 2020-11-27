<?php

namespace System\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Doctrine\ORM\Mapping as ORM;
use System\Entity\CronJob;
use System\Traits\StartedFinishedAtEntityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cronjobs_logs")
 * @ORM\HasLifecycleCallbacks
 */
class CronJobLog
{

    use ObjectSimpleHydrating;
    use StartedFinishedAtEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var CronJob
     * @ORM\ManyToOne(targetEntity="System\Entity\CronJob", inversedBy="logs")
     * @ORM\JoinColumn(name="cronjob_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $cronjob;

    /**
     * @var string
     * @ORM\Column(name="error_msg", type="text", nullable=true)
     */
    private $errorMsg;

    /**
     * @var string
     * @ORM\Column(name="stack_trace", type="text", nullable=true)
     */
    private $stackTrace;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cronjob
     * 
     * @param CronJob $cronjob
     * @return CronJobLog
     */
    public function setCronjob($cronjob)
    {
        $this->cronjob = $cronjob;

        return $this;
    }

    /**
     * Get cronjob
     * 
     * @return CronJob
     */
    public function getCronjob()
    {
        return $this->cronjob;
    }

    /**
     * Set errorMsg
     * 
     * @param string $errorMsg
     * @return CronJobLog
     */
    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;

        return $this;
    }

    /**
     * Get errorMsg
     * 
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * Set stackTrace
     * 
     * @param string $stackTrace
     * @return CronJobLog
     */
    public function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    /**
     * Get stackTrace
     * 
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

}