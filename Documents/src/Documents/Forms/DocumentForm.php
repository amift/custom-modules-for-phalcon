<?php

namespace Documents\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Tool\Enable;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Documents\Entity\Document;

class DocumentForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Document $entity
     * @param array $options
     * @return void
     */
    public function initialize(Document $entity = null, array $options = [])
    {
        $parent = $entity->getParent();
        if (is_object($parent)) {
            $entity->setParent($parent->getId());
        }

        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorStringLength = new StringLength([
            'max' => 255,
            'min' => 2,
            'messageMaximum' => "Max 255 chars allowed",
            'messageMinimum' => "At least 2 chars need"
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Key
        $attributes = [ 'class' => 'form-control', 'tabindex' => '1', 'placeholder' => 'Unique key' ];
        if ($entity->getId() > 0) {
            $attributes['disabled'] = 'disabled';
        }
        $key = new Text('key', $attributes);
        $key->setLabel('Key');
        $key->addValidators([
            $validatorRequired,
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Document::class),
                'fields' => [ 'key' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Document with given key already exists',
                ],
            ])
        ]);
        $this->add($key);

        // Title
        $title = new Text('title', [ 'class' => 'form-control', 'tabindex' => '2', 'placeholder' => 'Title' ]);
        $title->setLabel('Title');
        $title->addValidators([
            $validatorRequired,
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Document::class),
                'fields' => [ 'title' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Document with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Slug
        $slug = new Text('slug', [
            'placeholder' => 'URL parameter',
            'class' => 'form-control', 
            'tabindex' => '3'
        ]);
        $slug->setLabel('URL');
        $slug->addValidators([
            $validatorRequired,
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Document::class),
                'fields' => [ 'slug' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Document with given url parameter already exists',
                ],
            ])
        ]);
        $this->add($slug);

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

        // Parent
        $parantOptions = $this->getParentsOptionsList();
        $parent = new Select('parent', $parantOptions, [ 'class' => 'selectpicker', 'tabindex' => '5' ]);
        $parent->setLabel('Parent');
        $parent->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => array_keys($parantOptions)
            ])
        ]);
        $this->add($parent);

        // Content
        $content = new TextArea('content', [
            'class' => 'form-control',
            'tabindex' => '6',
            'style' => 'height: 370px;'
        ]);
        $content->setLabel('Content');
        $this->add($content);

        // SEO Title
        $seoTitle = new TextArea('seoTitle', [ 'class' => 'form-control', 'tabindex' => '7', 'style' => 'height: 100px;' ]);
        $seoTitle->setLabel('SEO Title');
        $seoTitle->addValidators([
            new StringLength([
                'max' => 255,
                'messageMaximum' => "Max 255 chars allowed",
            ]),
        ]);
        $this->add($seoTitle);

        // SEO Keywords
        $seoKeywords = new TextArea('seoKeywords', [ 'class' => 'form-control', 'tabindex' => '8', 'style' => 'height: 100px;' ]);
        $seoKeywords->setLabel('SEO keywords');
        $seoKeywords->addValidators([
            new StringLength([
                'max' => 255,
                'messageMaximum' => "Max 255 chars allowed",
            ]),
        ]);
        $this->add($seoKeywords);

        // SEO Description
        $seoDescription = new TextArea('seoDescription', [ 'class' => 'form-control', 'tabindex' => '9', 'style' => 'height: 100px;' ]);
        $seoDescription->setLabel('SEO Description');
        $seoDescription->addValidators([
            new StringLength([
                'max' => 255,
                'messageMaximum' => "Max 255 chars allowed",
            ]),
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
        $rows = $this->getEntityManager()->getRepository(Document::class)->getParentsList();

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
