<?php

namespace Statistics\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\KeyEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportParserResultRepository")
 * @ORM\Table(
 *      name="sport_parser_results",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_parser_results_unique_idx", columns={"`key`"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportParserResult 
{
    use ObjectSimpleHydrating;
    use KeyEntityTrait;
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
     * @var DateTime
     * @ORM\Column(name="parsed_at", type="datetime", nullable=true)
     */
    private $parsedAt;

    /**
     * @ORM\Column(name="parsed_data", type="json_array", nullable=true)
     */
    private $parsedData;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->parsedAt = new \DateTime('now');
        $this->parsedData = [];
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
     * Set parsedAt
     *
     * @param \DateTime $parsedAt
     * @return SportParserResult
     */
    public function setParsedAt($parsedAt)
    {
        $this->parsedAt = $parsedAt;

        return $this;
    }

    /**
     * Get parsedAt
     *
     * @return \DateTime 
     */
    public function getParsedAt()
    {
        return $this->parsedAt;
    }

    /**
     * Set parsedData
     *
     * @param array $parsedData
     * @return SportParserResult
     */
    public function setParsedData($parsedData)
    {
        $this->parsedData = $parsedData;

        return $this;
    }

    /**
     * Get parsedData
     *
     * @return array 
     */
    public function getParsedData()
    {
        return $this->parsedData;
    }

}
