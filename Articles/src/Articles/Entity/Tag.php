<?php

namespace Articles\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\TagRepository")
 * @ORM\Table(
 *      name="tags",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="tags_unique_idx", columns={"title"}),
 *      },
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Tag 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use TitleEntityTrait;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}
