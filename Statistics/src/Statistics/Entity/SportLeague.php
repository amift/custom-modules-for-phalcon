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
use Statistics\Entity\SportLeagueGroup;
use Statistics\Entity\SportType;

/**
 * @ORM\Entity(repositoryClass="Statistics\Repository\SportLeagueRepository")
 * @ORM\Table(
 *      name="sport_leagues",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="sport_leagues_unique_idx", columns={"`key`", "sport_type_id"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class SportLeague 
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
     * @var SportType
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportType", inversedBy="leagues")
     * @ORM\JoinColumn(name="sport_type_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $sportType;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Statistics\Entity\SportLeagueGroup", mappedBy="sportLeague", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $groups;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Category")
     * @ORM\JoinColumn(name="article_category_id_lvl2", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $articleCategoryLvl2;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->groups = new ArrayCollection();
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
     * Set sportType
     * 
     * @param SportType $sportType
     * @return SportLeague
     */
    public function setSportType($sportType)
    {
        $this->sportType = $sportType;

        return $this;
    }

    /**
     * Get sportType
     * 
     * @return SportType
     */
    public function getSportType()
    {
        return $this->sportType;
    }

    /**
     * Unset sportType
     * 
     * @return SportLeague
     */
    public function unsetSportType()
    {
        $this->sportType = null;

        return $this;
    }

    /**
     * Set groups
     * 
     * @param $groups
     * @return SportLeague
     */
    public function setGroups($groups) 
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups() 
    {
        return $this->groups;
    }

    /**
     * Add group
     * 
     * @param SportLeagueGroup $group
     * @return SportLeague
     */
    public function addGroup(SportLeagueGroup $group)
    {
        if (! $this->groups->contains($group)) {
            $this->groups->add($group);
            $group->setSportLeague($this);
        }
        return $this;
    }

    /**
     * Remove group
     * 
     * @param SportLeagueGroup $group
     * @return SportLeague
     */
    public function removeGroup(SportLeagueGroup $group)
    {
        if ($this->groups->contains($group)) {
            $group->unsetSportLeague();
            $this->groups->removeElement($group);
        }
        return $this;
    }

    /**
     * Get groups data for output in form
     * 
     * @return array
     */
    public function getFormattedOptionsForForm()
    {
        $data = [];

        foreach ($this->groups as $group) {
            /* @var $group SportLeagueGroup */

            $data[] = [
                'id' => $group->getId(),
                'title' => $group->getTitle(),
                'key' => $group->getKey()
            ];
        }

        return $data;
    }

    /**
     * Get SportLeagueGroup by key
     * 
     * @param string $key
     * @return null|SportLeagueGroup
     */
    public function getLeagueGroupByKey($key = '')
    {
        if ($key === '') {
            return null;
        }

        return $this->getGroups()->filter(function ($group) use ($key) {
            /* @var $group SportLeagueGroup */

            if ((string)$group->getKey() === (string)$key) {
                return $group;
            }
        })->first();
    }

    /**
     * Set articleCategoryLvl2
     * 
     * @param Category $articleCategoryLvl2
     * @return SportLeague
     */
    public function setArticleCategoryLvl2($articleCategoryLvl2)
    {
        $this->articleCategoryLvl2 = $articleCategoryLvl2;

        return $this;
    }

    /**
     * Get articleCategoryLvl2
     * 
     * @return Category
     */
    public function getArticleCategoryLvl2()
    {
        return $this->articleCategoryLvl2;
    }

}
