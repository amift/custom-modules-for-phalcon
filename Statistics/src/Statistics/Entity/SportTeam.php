<?php

namespace Statistics\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\KeyEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Traits\SportLeagueEntityTrait;
use Statistics\Traits\SportTypeEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportTeamRepository")
 * @ORM\Table(
 *      name="sport_teams",
 *      indexes={
 *          @ORM\Index(name="sport_teams_key_idx", columns={"`key`"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_teams_unique_idx", columns={"`key`", "sport_type_id", "sport_league_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportTeam 
{
    use ObjectSimpleHydrating;
    use KeyEntityTrait;
    use TitleEntityTrait;
    use SportTypeEntityTrait;
    use SportLeagueEntityTrait;
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
     * @var string
     * @ORM\Column(name="flag_code", type="string", length=2, nullable=true)
     */
    private $flagCode;

    /**
     * @var string
     * @ORM\Column(name="country", type="string", length=3, nullable=true)
     */
    private $country;

    /**
     * @var string
     * @ORM\Column(name="additional_info", type="string", nullable=true)
     */
    private $additionalInfo;

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
     * Set flagCode
     *
     * @param string $flagCode
     * @return SportTeam
     */
    public function setFlagCode($flagCode)
    {
        $this->flagCode = $flagCode;

        return $this;
    }

    /**
     * Get flagCode
     *
     * @return string
     */
    public function getFlagCode()
    {
        return $this->flagCode;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return SportTeam
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set additionalInfo
     *
     * @param string $additionalInfo
     * @return SportTeam
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    /**
     * Get additionalInfo
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

}
