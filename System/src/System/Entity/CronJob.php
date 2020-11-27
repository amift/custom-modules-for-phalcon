<?php

namespace System\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\EnabledEntityTrait;
use Common\Traits\TitleEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use System\Traits\CronJobLogicAwareTrait;
use System\Traits\StartedFinishedAtEntityTrait;

/**
 * @ORM\Entity(repositoryClass="System\Repository\CronJobRepository")
 * @ORM\Table(
 *      name="cronjobs",
 *      indexes={
 *          @ORM\Index(name="cronjobs_enabled_idx", columns={"enabled"}),
 *          @ORM\Index(name="cronjobs_state_idx", columns={"state"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class CronJob 
{

    use ObjectSimpleHydrating;
    use EnabledEntityTrait;
    use TitleEntityTrait;
    use StartedFinishedAtEntityTrait;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use CronJobLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", options={"unsigned":true}, nullable=false, options={"default":0})
     */
    private $state;

    /**
     * @var string
     * @ORM\Column(name="`cron_action`", type="string", length=255, nullable=false)
     */
    private $cronAction;

    /**
     * @var string
     * @ORM\Column(name="`cron_expr`", type="string", length=255, nullable=false)
     */
    private $cronExpr;

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $property
     * @ORM\OneToMany(targetEntity="System\Entity\CronJobLog", mappedBy="cronjob", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"launchedAt"="DESC"})
     */
    private $logs;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->state = 0;
        $this->logs = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString() 
    {
        return sprintf('CronJob: %s', $this->title);
    }

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
     * Set state
     *
     * @param smallint $state
     * @return CronJob
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return smallint 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set cronAction
     * 
     * @param string $cronAction
     * @return CronJob
     */
    public function setCronAction($cronAction)
    {
        $this->cronAction = $cronAction;

        return $this;
    }

    /**
     * Get cronAction
     * 
     * @return string
     */
    public function getCronAction()
    {
        return $this->cronAction;
    }

    /**
     * Set cronExpr
     * 
     * @param string $cronExpr
     * @return CronJob
     */
    public function setCronExpr($cronExpr)
    {
        $this->cronExpr = $cronExpr;

        return $this;
    }

    /**
     * Get cronExpr
     * 
     * @return string
     */
    public function getCronExpr()
    {
        return $this->cronExpr;
    }

    /**
     * Set logs
     * 
     * @param $logs
     * @return CronJob
     */
    public function setLogs($logs) 
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * Get logs
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    public function addLog(\System\Entity\CronJobLog $log)
    {
        $log->setCronjob($this);
        $this->logs->add($log);

        return $this;
    }

    public function addLogs($logs)
    {
        foreach ($logs as $log) {
            $log->setCronjob($this);
            $this->logs->add($log);
        }

        return $this;
    }

    public function removeLogs($logs)
    {
        foreach ($logs as $log) {
            $this->logs->removeElement($log);
        }

        return $this;
    }

}