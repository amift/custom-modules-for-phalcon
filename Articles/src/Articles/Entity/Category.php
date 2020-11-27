<?php

namespace Articles\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\EnabledEntityTrait;
use Common\Traits\SlugEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\LevelEntityTrait;
use Common\Traits\OrderingEntityTrait;
use Common\Traits\MetaDataEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Articles\Traits\CategoryArticlesEntityTrait;
use Articles\Traits\CategoryParentChildrensEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\CategoryRepository")
 * @ORM\Table(
 *      name="categories",
 *      indexes={
 *          @ORM\Index(name="categories_enabled_idx", columns={"enabled"}),
 *          @ORM\Index(name="categories_slug_idx", columns={"slug"}),
 *          @ORM\Index(name="categories_show_in_menu_idx", columns={"show_in_menu"}),
 *          @ORM\Index(name="categories_lvl_idx", columns={"lvl"}),
 *          @ORM\Index(name="categories_ordering_idx", columns={"ordering"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Category 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use EnabledEntityTrait;
    use SlugEntityTrait;
    use TitleEntityTrait;
    use LevelEntityTrait;
    use OrderingEntityTrait;
    use CategoryArticlesEntityTrait;
    use CategoryParentChildrensEntityTrait;
    use MetaDataEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean
     * @ORM\Column(name="show_in_menu", type="boolean", nullable=false, options={"default":true})
     */
    private $showInMenu = true;

    /**
     * @var string
     * @ORM\Column(name="`image`", type="string", nullable=true)
     */
    private $image;

    /**
     * @var string
     * @ORM\Column(name="`image_path`", type="string", nullable=true)
     */
    private $imagePath;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setEnabled(true);
        $this->setShowInMenu(true);
        $this->setLevel(1);
        $this->childrens = new ArrayCollection();
        $this->articlesLvl1 = new ArrayCollection();
        $this->articlesLvl2 = new ArrayCollection();
        $this->articlesLvl3 = new ArrayCollection();
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
     * Set showInMenu
     * 
     * @param boolean $showInMenu
     * @return Category
     */
    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    /**
     * Get showInMenu
     * 
     * @return boolean 
     */
    public function getShowInMenu()
    {
        return $this->showInMenu ? 1 : 0;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Category
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check if image is filled or not
     * 
     * @return bool
     */
    public function hasImage()
    {
        return $this->image !== null && $this->image !== '' ? true : false;
    }

    /**
     * Set imagePath
     *
     * @param string $imagePath
     * @return Category
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function getServerPublicPath()
    {
        return str_replace('/', DS, sprintf('%s/public/', ROOT_PATH));
    }

    public function getImageDirectory()
    {
        $date = new \DateTime('now');

        $path = str_replace('/', DS, sprintf(
            '%smedia/categories',
            $this->getServerPublicPath()
        ));

        return $path;
    }

    public function getImagePublicPath()
    {
        if ($this->imagePath !== '') {
            $orignalPublicPath = DS . str_replace($this->getServerPublicPath(), '', $this->imagePath);
            $orignalPublicPath = str_replace(DS, '/', $orignalPublicPath);

            return $orignalPublicPath;
        }
        
        return '';
    }
}
