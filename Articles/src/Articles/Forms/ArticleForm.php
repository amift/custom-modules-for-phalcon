<?php

namespace Articles\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Forms\Element\DateTimeField;
use Common\Tool\Enable;
use Common\Forms\Validator\RequiredWhenFieldValueValidator;
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
use Phalcon\Validation\Validator\Url;
use Articles\Entity\Article;
use Articles\Entity\Category;
use Articles\Tool\State;
use Articles\Tool\Type;

class ArticleForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Article $entity
     * @param array $options
     * @return void
     */
    public function initialize(Article $entity = null, array $options = [])
    {
        $categoryLvl1 = $entity->getCategoryLvl1();
        if (is_object($categoryLvl1)) {
            $entity->setCategoryLvl1($categoryLvl1->getId());
        }
        $categoryLvl2 = $entity->getCategoryLvl2();
        if (is_object($categoryLvl2)) {
            $entity->setCategoryLvl2($categoryLvl2->getId());
        }
        $categoryLvl3 = $entity->getCategoryLvl3();
        if (is_object($categoryLvl3)) {
            $entity->setCategoryLvl3($categoryLvl3->getId());
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
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Article::class),
                'fields' => [ 'title', 'categoryLvl1', 'categoryLvl2', 'categoryLvl3' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Article with given title in choosen categories already exists',
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
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Article::class),
                'fields' => [ 'slug' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Article with given url parameter already exists',
                ],
            ])
        ]);
        $this->add($slug);

        // Publication date
        $date = $entity->getPublicationDate();
        if (!is_object($date)) {
            $date = new \DateTime('now');
        }
        $entity->setPublicationDate($date->format('d/m/Y H:i:s'));
        $publicationDate = new DateTimeField('publicationDate', [ 'class' => 'form-control' ]);
        $publicationDate->setLabel('Date');
        $publicationDate->setFieldId('publicationDate');
        $this->add($publicationDate);

        // Source URL
        $sourceUrl = new Text('sourceUrl', [ 'class' => 'form-control', 'placeholder' => 'Source URL' ]);
        $sourceUrl->setLabel('Source URL');
        $sourceUrl->addValidators([
            $validatorRequired,
            $validatorStringLength,
            new Url([
                'message' => 'Invalid URL value'
            ])
        ]);
        $this->add($sourceUrl);

        // Type
        $type = new Select('type', Type::getLabels(), [ 'class' => 'selectpicker' ]);
        $type->setLabel('Type');
        $type->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Type::getTypes()
            ])
        ]);
        $this->add($type);

        // Image
        $file = new File('image', [ 'class' => 'form-control' ]);
        $file->setLabel('Image');
        $this->add($file);

        // Media Source Name
        $mediaSourceName = new Text('mediaSourceName', [ 'class' => 'form-control', 'placeholder' => 'Author name' ]);
        $mediaSourceName->setLabel('Author name');
        $mediaSourceName->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($mediaSourceName);

        // Media Source URL
        $mediaSourceUrl = new Text('mediaSourceUrl', [ 'class' => 'form-control', 'placeholder' => 'Author URL' ]);
        $mediaSourceUrl->setLabel('Author URL');
        $mediaSourceUrl->addValidators([
            $validatorMaxStringLength,
            new Url([
                'message' => 'Invalid URL value',
                'allowEmpty' => true
            ])
        ]);
        $this->add($mediaSourceUrl);

        // Video
        $video = new Text('video', [ 'class' => 'form-control', 'placeholder' => 'Video source' ]);
        $video->setLabel('Video');
        $video->addValidators([
            new RequiredWhenFieldValueValidator([
                'field_name' => 'type',
                'field_value' => '2',
                'messages' => [
                    RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Video is required',
                ],
            ]),
            $validatorMaxStringLength,
            new Url([
                'message' => 'Invalid URL value',
                'allowEmpty' => true
            ])
        ]);
        $this->add($video);

        // State
        $state = new Select('state', State::getLabels(), [ 'class' => 'selectpicker' ]);
        $state->setLabel('Status');
        $state->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => State::getStates()
            ])
        ]);
        $this->add($state);

        // Startpage
        $startpage = new Select('startpage', Enable::getShortLabels(), [ 'class' => 'selectpicker' ]);
        $startpage->setLabel('Startpage');
        $startpage->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($startpage);

        // Promo
        $promo = new Select('promo', Enable::getShortLabels(), [ 'class' => 'selectpicker' ]);
        $promo->setLabel('Promo');
        $promo->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($promo);

        // Actual
        $actual = new Select('actual', Enable::getShortLabels(), [ 'class' => 'selectpicker' ]);
        $actual->setLabel('Actual');
        $actual->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($actual);

        // Category 1
        $parant1Options = $this->getParentsOptionsList(1);
        $keys = array_keys($parant1Options);
        if (isset($keys[0])) unset($keys[0]);
        $categoryLvl1 = new Select('categoryLvl1', $parant1Options, [ 'class' => 'selectpicker' ]);
        $categoryLvl1->setLabel('Category 1');
        $categoryLvl1->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => $keys, //array_keys($parant1Options)
            ])
        ]);
        $this->add($categoryLvl1);

        // Category 2
        $parant2Options = $parant2OptionsToSelect = $this->getParentsOptionsList(2);
        if ((int)$entity->getCategoryLvl1() > 0) {
            $parant2OptionsToSelect = $this->getParentsOptionsList(2, $entity->getCategoryLvl1());
        }
        $categoryLvl2 = new Select('categoryLvl2', $parant2OptionsToSelect, [ 'class' => 'selectpicker' ]);
        $categoryLvl2->setLabel('Category 2');
        $categoryLvl2->addValidators([
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => array_keys($parant2Options)
            ])
        ]);
        $this->add($categoryLvl2);

        // Category 3
        $parant3Options = $parant3OptionsToSelect = $this->getParentsOptionsList(3);
        if ((int)$entity->getCategoryLvl2() > 0) {
            $parant3OptionsToSelect = $this->getParentsOptionsList(3, $entity->getCategoryLvl2());
        }
        $categoryLvl3 = new Select('categoryLvl3', $parant3OptionsToSelect, [ 'class' => 'selectpicker' ]);
        $categoryLvl3->setLabel('Category 3');
        $categoryLvl3->addValidators([
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => array_keys($parant3Options)
            ])
        ]);
        $this->add($categoryLvl3);

        // Summary
        $summary = new TextArea('summary', [ 'class' => 'form-control', 'style' => 'height: 100px;' ]);
        $summary->setLabel('Summary');
        $summary->addValidators([
            $validatorRequired
        ]);
        $this->add($summary);

        // Content
        $content = new TextArea('content', [
            'class' => 'form-control',
            'style' => 'height: 370px;'
        ]);
        $content->setLabel('Content');
        $content->addValidators([
            new RequiredWhenFieldValueValidator([
                'field_name' => 'type',
                'field_value' => '1',
                'messages' => [
                    RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Content is required',
                ],
            ])
        ]);
        $this->add($content);

        /*// SEO Title
        $seoTitle = new TextArea('seoTitle', [ 'class' => 'form-control', 'style' => 'height: 75px;' ]);
        $seoTitle->setLabel('Meta title');
        $seoTitle->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($seoTitle);

        // SEO Keywords
        $seoKeywords = new TextArea('seoKeywords', [ 'class' => 'form-control', 'style' => 'height: 75px;' ]);
        $seoKeywords->setLabel('Meta keywords');
        $seoKeywords->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($seoKeywords);

        // SEO Description
        $seoDescription = new TextArea('seoDescription', [ 'class' => 'form-control', 'style' => 'height: 75px;' ]);
        $seoDescription->setLabel('Meta description');
        $seoDescription->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($seoDescription);*/

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
    protected function getParentsOptionsList($level = 1, $parentId = null)
    {
        $list = $this->getEntityManager()->getRepository(Category::class)->getParentsListFromLevel($level, $parentId);

        $rows = [
            '0' => '-'
        ];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

}
