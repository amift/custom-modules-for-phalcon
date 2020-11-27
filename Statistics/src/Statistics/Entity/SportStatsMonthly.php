<?php

namespace Statistics\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Traits\StatsDateMonthlyEntityTrait;
use Statistics\Traits\StatsDataEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportStatsMonthlyRepository")
 * @ORM\Table(
 *      name="sport_stats_monthly",
 *      indexes={
 *          @ORM\Index(name="sport_stats_monthly_year_idx", columns={"`year`"}),
 *          @ORM\Index(name="sport_stats_monthly_month_idx", columns={"`month`"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_stats_monthly_unique_idx", columns={"`year`", "`month`", "sport_season_id", "sport_league_group_id", "sport_team_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportStatsMonthly 
{
    use ObjectSimpleHydrating;
    use StatsDateMonthlyEntityTrait;
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
        //
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
