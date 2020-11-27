<?php

namespace Articles\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Tool\Enable;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\File as FileValidator;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Articles\Entity\Category;

class CategoryForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Category $entity
     * @param array $options
     * @return void
     */
    public function initialize(Category $entity = null, array $options = [])
    {
        $parent = $entity->getParent();
        if (is_object($parent)) {
            $entity->setParent($parent->getId());
        }

        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorNamesLength = new StringLength([
            'max' => 255,
            'min' => 2,
            'messageMaximum' => "Max 255 chars allowed",
            'messageMinimum' => "At least 2 chars need"
        ]);

        $validatorMaxStringLength = new StringLength([
            'max' => 255,
            'messageMaximum' => "Max 255 chars allowed",
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Title
        $title = new Text('title', [ 'class' => 'form-control', 'tabindex' => '1', 'placeholder' => 'Title' ]);
        $title->setLabel('Title');
        $title->addValidators([
            $validatorRequired,
            $validatorNamesLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Category::class),
                'fields' => [ 'title', 'parent' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Category with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Slug
        $slug = new Text('slug', [
            'placeholder' => 'URL parameter',
            'class' => 'form-control', 
            'tabindex' => '2'
        ]);
        $slug->setLabel('URL');
        $slug->addValidators([
            $validatorRequired,
            $validatorNamesLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Category::class),
                'fields' => [ 'slug', 'parent' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Category with given url parameter already exists',
                ],
            ])
        ]);
        $this->add($slug);

        // Parent
        $parantOptions = $this->getParentsOptionsList();
        $parent = new Select('parent', $parantOptions, [ 'class' => 'selectpicker', 'tabindex' => '3' ]);
        $parent->setLabel('Parent');
        $parent->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => array_keys($parantOptions)
            ])
        ]);
        $this->add($parent);

        // Enabled
        $enabled = new Select('enabled', Enable::getLabels(), [ 'class' => 'selectpicker', 'tabindex' => '4' ]);
        $enabled->setLabel('Enabled');
        $enabled->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($enabled);

        // ShowInMenu
        $showInMenu = new Select('showInMenu', Enable::getShortLabels(), [ 'class' => 'selectpicker', 'tabindex' => '5' ]);
        $showInMenu->setLabel('Show in menu');
        $showInMenu->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($showInMenu);

        // Image
        $file = new File('image', [ 'class' => 'form-control' ]);
        $file->setLabel('Image');
        $this->add($file);

        // SEO Title
        $seoTitle = new TextArea('seoTitle', [ 'class' => 'form-control', 'tabindex' => '6', 'style' => 'height: 75px;' ]);
        $seoTitle->setLabel('Meta title');
        $seoTitle->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($seoTitle);

        // SEO Keywords
        $seoKeywords = new TextArea('seoKeywords', [ 'class' => 'form-control', 'tabindex' => '7', 'style' => 'height: 75px;' ]);
        $seoKeywords->setLabel('Meta keywords');
        $seoKeywords->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($seoKeywords);

        // SEO Description
        $seoDescription = new TextArea('seoDescription', [ 'class' => 'form-control', 'tabindex' => '8', 'style' => 'height: 75px;' ]);
        $seoDescription->setLabel('Meta description');
        $seoDescription->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($seoDescription);

        // CSRF
        $csrf = new Hidden('csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);

        parent::initialize();
    }

    /**
     * Get EntityManager.
     * 
     * @access protected
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if ($this->_em === null || !$this->_em) {
            $this->_em = $this->di->getEntityManager();
        }

        return $this->_em;
    }

    /**
     * @return array
     */
    protected function getParentsOptionsList()
    {
        $rows = $this->getEntityManager()->getRepository(Category::class)->getParentsList();

        $tmp = [];
        foreach ($rows as $row) {
            if ($row['parent'] === null) {
                $tmp[$row['id']] = [
                    'title' => $row['title'],
                    'id' => $row['id'],
                    'childs' => []
                ];
            } else {
                $tmp[$row['parent']]['childs'][$row['id']] = [
                    'title' => $row['title'],
                    'id' => $row['id'],
                    'slug' => $row['slug'],
                ];
            }
        }

        $rows = [
            '0' => 'Root'
        ];
        foreach ($tmp as $item) {
            $rows[$item['id']] = sprintf('- %s', $item['title']);
            if (count($item['childs']) > 0) {
                foreach ($item['childs'] as $childItem) {
                    $rows[$childItem['id']] = sprintf('-- %s', $childItem['title']);
                }
            }
        }

        return $rows;
    }

}
