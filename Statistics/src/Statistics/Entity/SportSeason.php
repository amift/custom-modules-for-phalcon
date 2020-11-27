<?php

namespace Statistics\Entity;

use Common\Traits\ActualEntityTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\KeyEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Traits\SportLeagueEntityTrait;
use Statistics\Traits\SportTypeEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportSeasonRepository")
 * @ORM\Table(
 *      name="sport_seasons",
 *      indexes={
 *          @ORM\Index(name="sport_seasons_key_idx", columns={"`key`"}),
 *          @ORM\Index(name="sport_seasons_actual_idx", columns={"`actual`"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_seasons_unique_idx", columns={"`key`", "sport_type_id", "sport_league_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportSeason 
{
    use ObjectSimpleHydrating;
    use ActualEntityTrait;
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
     * @ORM\Column(name="import_api_url", type="string", length=250, nullable=true)
     */
    private $importApiUrl;

    /**
     * @var \DateTime
     * @ORM\Column(name="import_api_actual_date", type="datetime", nullable=true, options={"default":null})
     */
    private $importApiActualDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_import_from_api_at", type="datetime", nullable=true, options={"default":null})
     */
    private $lastImportFromApiAt;

    /**
     * @var integer
     * @ORM\Column(name="priority", type="integer", nullable=false, options={"unsigned":true,"default":1})
     */
    private $priority;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->setPriority(1);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $league = $this->getSportLeague();

        if (is_object($league)) {
            return sprintf('%s %s', $league->getTitle(), $this->getTitle());
        }

        return sprintf('%s', $this->getTitle());
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
     * Set importApiUrl
     *
     * @param string $importApiUrl
     * @return SportSeason
     */
    public function setImportApiUrl($importApiUrl)
    {
        $this->importApiUrl = $importApiUrl;

        return $this;
    }

    /**
     * Get importApiUrl
     *
     * @return string
     */
    public function getImportApiUrl()
    {
        return $this->importApiUrl;
    }

    /**
     * Set importApiActualDate
     *
     * @param \DateTime $importApiActualDate
     * @return SportSeason
     */
    public function setImportApiActualDate($importApiActualDate)
    {
        $this->importApiActualDate = $importApiActualDate;

        return $this;
    }

    /**
     * Get importApiActualDate
     *
     * @return \DateTime
     */
    public function getImportApiActualDate()
    {
        return $this->importApiActualDate;
    }

    /**
     * Set lastImportFromApiAt
     *
     * @param \DateTime $lastImportFromApiAt
     * @return SportSeason
     */
    public function setLastImportFromApiAt($lastImportFromApiAt)
    {
        $this->lastImportFromApiAt = $lastImportFromApiAt;

        return $this;
    }

    /**
     * Get lastImportFromApiAt
     *
     * @return \DateTime
     */
    public function getLastImportFromApiAt()
    {
        return $this->lastImportFromApiAt;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return SportSeason
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

}
