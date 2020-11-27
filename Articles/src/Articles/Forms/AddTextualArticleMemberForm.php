<?php

namespace Articles\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Url;
use Articles\Entity\Article;
use Articles\Entity\Category;

class AddTextualArticleMemberForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * @var 
     */
    protected $_translator;

    /**
     * Form initialize
     * 
     * @access public
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
            'message' => $this->getTranslator()->trans('error_field_required', 'Lauks ir obligāti aizpildāms')
        ]);

        $validatorMaxStringLength = new StringLength([
            'max' => 255,
            'messageMaximum' => sprintf($this->getTranslator()->trans('error_max_xxx_chars', "Ne vairāk kā %s simboli ir atļauti"), 255),
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Category 1
        $parant1Options = $this->getParentsOptionsList(1);
        $keys = array_keys($parant1Options);
        if (isset($keys[0])) unset($keys[0]);
        $categoryLvl1 = new Select('categoryLvl1', $parant1Options, [ 'tabindex' => '10' ]);
        $categoryLvl1->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => $this->getTranslator()->trans('error_field_undefined', 'Nepieciešams izvēlēties kādu no variantiem'),
                'domain' => $keys,
            ])
        ]);
        $this->add($categoryLvl1);

        // Category 2
        $parant2Options = $parant2OptionsToSelect = $this->getParentsOptionsList(2);
        if ((int)$entity->getCategoryLvl1() > 0) {
            $parant2OptionsToSelect = $this->getParentsOptionsList(2, $entity->getCategoryLvl1());
        }
        $categoryLvl2 = new Select('categoryLvl2', $parant2OptionsToSelect, [ 'tabindex' => '11' ]);
        $categoryLvl2->addValidators([
            new InclusionIn([
                'message' => $this->getTranslator()->trans('error_field_undefined', 'Nepieciešams izvēlēties kādu no variantiem'),
                'domain' => array_keys($parant2Options)
            ])
        ]);
        $this->add($categoryLvl2);

        // Category 3
        $parant3Options = $parant3OptionsToSelect = $this->getParentsOptionsList(3);
        if ((int)$entity->getCategoryLvl2() > 0) {
            $parant3OptionsToSelect = $this->getParentsOptionsList(3, $entity->getCategoryLvl2());
        }
        $categoryLvl3 = new Select('categoryLvl3', $parant3OptionsToSelect, [ 'tabindex' => '12' ]);
        $categoryLvl3->addValidators([
            new InclusionIn([
                'message' => $this->getTranslator()->trans('error_field_undefined', 'Nepieciešams izvēlēties kādu no variantiem'),
                'domain' => array_keys($parant3Options)
            ])
        ]);
        $this->add($categoryLvl3);

        // Title
        $title = new Text('title', [ 'tabindex' => '13' ]);
        $title->addValidators([
            $validatorRequired,
            new StringLength([
                'max' => 140,
                'min' => 20,
                'messageMaximum' => sprintf($this->getTranslator()->trans('error_max_xxx_chars', "Ne vairāk kā %s simboli ir atļauti"), 140),
                'messageMinimum' => sprintf($this->getTranslator()->trans('error_min_xxx_chars', "Vismaz %s simboli jānorāda"), 20)
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Article::class),
                'fields' => [ 'title', 'categoryLvl1', 'categoryLvl2', 'categoryLvl3' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => $this->getTranslator()->trans('error_article_title_in_choosen_categories_exists', 'Ziņa ar norādīto virsrakstu izvēlētajās kategorijās jau eksistē'),
                ],
            ])
        ]);
        $this->add($title);

        // Media Source Name
        $mediaSourceName = new Text('mediaSourceName', [ 'tabindex' => '14' ]);
        $mediaSourceName->addValidators([
            $validatorMaxStringLength,
        ]);
        $this->add($mediaSourceName);

        // Media Source URL
        $mediaSourceUrl = new Text('mediaSourceUrl', [ 'placeholder' => 'http://', 'tabindex' => '15' ]);
        $mediaSourceUrl->addValidators([
            $validatorMaxStringLength,
            new Url([
                'message' => $this->getTranslator()->trans('error_invalid_url', 'Neatbilstoša URL adrese'),
                'allowEmpty' => true
            ])
        ]);
        $this->add($mediaSourceUrl);

        // Image
        $file = new File('image', [ 'tabindex' => '16', 'style' => 'display: none;' ]);
        $this->add($file);

        // Summary
        $summary = new TextArea('summary', [ 'class' => 'textarea-md', 'tabindex' => '17' ]);
        $summary->addValidators([
            $validatorRequired,
            new StringLength([
                'max' => 300,
                'min' => 140,
                'messageMaximum' => sprintf($this->getTranslator()->trans('error_max_xxx_chars', "Ne vairāk kā %s simboli ir atļauti"), 300),
                'messageMinimum' => sprintf($this->getTranslator()->trans('error_min_xxx_chars', "Vismaz %s simboli jānorāda"), 140)
            ])
        ]);
        $this->add($summary);

        // Content
        $content = new TextArea('content', [ 'class' => 'textarea-lg', 'tabindex' => '18' ]);
        $content->addValidators([
            $validatorRequired,
            new StringLength([
                'max' => 5000,
                'min' => 300,
                'messageMaximum' => sprintf($this->getTranslator()->trans('error_max_xxx_chars', "Ne vairāk kā %s simboli ir atļauti"), 5000),
                'messageMinimum' => sprintf($this->getTranslator()->trans('error_min_xxx_chars', "Vismaz %s simboli jānorāda"), 300)
            ])
        ]);
        $this->add($content);

        // Source URL
        $sourceUrl = new Text('sourceUrl', [ 'placeholder' => 'http://', 'tabindex' => '19' ]);
        $sourceUrl->addValidators([
            $validatorRequired,
            $validatorMaxStringLength,
            new Url([
                'message' => $this->getTranslator()->trans('error_invalid_url', 'Neatbilstoša URL adrese'),
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Article::class),
                'fields' => [ 'sourceUrl' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => $this->getTranslator()->trans('error_article_source_exists', 'Ziņa ar norādīto oriģinālavotu jau eksistē'),
                ],
            ])
        ]);
        $this->add($sourceUrl);

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
     * Get translator. 
     * 
     * @access protected
     * @return Translator
     */
    protected function getTranslator()
    {
        if ($this->_translator === null || !$this->_translator) {
            $this->_translator = $this->di->getTranslator();
        }

        return $this->_translator;
    }

    /**
     * @access protected
     * @return array
     */
    protected function getParentsOptionsList($level = 1, $parentId = null, $onlyEnabled = true)
    {
        $list = $this->getEntityManager()->getRepository(Category::class)->getParentsListFromLevel($level, $parentId, $onlyEnabled);

        $rows = [
            '0' => '-'
        ];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

}
