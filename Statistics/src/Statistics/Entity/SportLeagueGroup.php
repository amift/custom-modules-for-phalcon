<?php

namespace Statistics\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\KeyEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Entity\SportLeague;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportLeagueGroupRepository")
 * @ORM\Table(
 *      name="sport_leagues_groups",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_leagues_groups_unique_idx", columns={"`key`", "sport_league_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportLeagueGroup 
{
    use ObjectSimpleHydrating;
    use KeyEntityTrait;
    use TitleEntityTrait;
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
     * @var SportLeague
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportLeague", inversedBy="groups")
     * @ORM\JoinColumn(name="sport_league_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $sportLeague;

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
        return sprintf('%s', $this->title);
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
     * Set sportLeague
     * 
     * @param SportLeague $sportLeague
     * @return SportLeagueGroup
     */
    public function setSportLeague($sportLeague)
    {
        $this->sportLeague = $sportLeague;

        return $this;
    }

    /**
     * Get sportLeague
     * 
     * @return SportLeague
     */
    public function getSportLeague()
    {
        return $this->sportLeague;
    }

    /**
     * Unset sportLeague
     * 
     * @return SportLeagueGroup
     */
    public function unsetSportLeague()
    {
        $this->sportLeague = null;

        return $this;
    }

}
