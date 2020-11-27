<?php

namespace Statistics\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Traits\StatsDataEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportStatsOverallRepository")
 * @ORM\Table(
 *      name="sport_stats_overall",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_stats_overall_unique_idx", columns={"sport_season_id", "sport_league_group_id", "sport_team_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportStatsOverall 
{
    use ObjectSimpleHydrating;
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
