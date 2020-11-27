<?php

namespace Statistics\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Traits\StatsDateTimeEntityTrait;
use Statistics\Traits\StatsDataEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportStatsDailyRepository")
 * @ORM\Table(
 *      name="sport_stats_daily",
 *      indexes={
 *          @ORM\Index(name="sport_stats_daily_date_idx", columns={"`date`"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_stats_daily_unique_idx", columns={"`date`", "sport_season_id", "sport_league_group_id", "sport_team_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportStatsDaily 
{
    use ObjectSimpleHydrating;
    use StatsDateTimeEntityTrait;
    use StatsDataEntityTrait;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->setDate(new \DateTime('now'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s', $this->id);
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

}
