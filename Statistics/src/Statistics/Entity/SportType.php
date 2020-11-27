<?php

namespace Statistics\Entity;

use Articles\Entity\Category;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\KeyEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Statistics\Entity\SportLeague;
use Statistics\Tool\StatsField;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportTypeRepository")
 * @ORM\Table(
 *      name="sport_types",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_types_unique_idx", columns={"`key`"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportType 
{
    use ObjectSimpleHydrating;
    use KeyEntityTrait;
    use TitleEntityTrait;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="smallint", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var array
     * @ORM\Column(name="table_fields", type="json_array", nullable=true)
     */
    private $tableFields;

    /**
     * @var string
     * @ORM\Column(name="score_field", type="string", nullable=false, options={"default":"score"})
     */
    private $scoreField;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Statistics\Entity\SportLeague", mappedBy="sportType", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $leagues;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Category")
     * @ORM\JoinColumn(name="article_category_id_lvl1", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $articleCategoryLvl1;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->leagues = new ArrayCollection();
        $this->tableFields = [];
        $this->scoreField = StatsField::FIELD_SCORE;
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
     * @return smallint
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tableFields
     *
     * @param array $tableFields
     * @return SportType
     */
    public function setTableFields($tableFields)
    {
        $this->tableFields = $tableFields;

        return $this;
    }

    /**
     * Get tableFields
     *
     * @return array 
     */
    public function getTableFields()
    {
        return $this->tableFields;
    }

    /**
     * Set scoreField
     *
     * @param string $scoreField
     * @return SportType
     */
    public function setSoreField($scoreField)
    {
        $this->scoreField = $scoreField;

        return $this;
    }

    /**
     * Get scoreField
     *
     * @return array 
     */
    public function getSoreField()
    {
        return $this->scoreField;
    }

    /**
     * Set leagues
     * 
     * @param $leagues
     * @return SportType
     */
    public function setLeagues($leagues) 
    {
        $this->leagues = $leagues;

        return $this;
    }

    /**
     * Get leagues
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeagues() 
    {
        return $this->leagues;
    }

    /**
     * Add league
     * 
     * @param SportLeague $league
     * @return SportType
     */
    public function addLeague(SportLeague $league)
    {
        if (! $this->leagues->contains($league)) {
            $this->leagues->add($league);
            $league->setSportType($this);
        }
        return $this;
    }

    /**
     * Remove league
     * 
     * @param SportLeague $league
     * @return SportType
     */
    public function removeLeague(SportLeague $league)
    {
        if ($this->leagues->contains($league)) {
            $league->unsetSportType();
            $this->leagues->removeElement($league);
        }
        return $this;
    }

    /**
     * Set articleCategoryLvl1
     * 
     * @param Category $articleCategoryLvl1
     * @return SportLeague
     */
    public function setArticleCategoryLvl1($articleCategoryLvl1)
    {
        $this->articleCategoryLvl1 = $articleCategoryLvl1;

        return $this;
    }

    /**
     * Get articleCategoryLvl1
     * 
     * @return Category
     */
    public function getArticleCategoryLvl1()
    {
        return $this->articleCategoryLvl1;
    }

}
