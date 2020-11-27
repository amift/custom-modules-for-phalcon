<?php

namespace System\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Tool\Enable;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use System\Entity\CronJob;

class CronJobForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param CronJob $entity
     * @param array $options
     * @return void
     */
    public function initialize(CronJob $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorStringLength = new StringLength([
            'max' => 255,
            'messageMaximum' => "Max 255 chars allowed"
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Title
        $title = new Text('title', [
            'placeholder' => 'Title',
            'class' => 'form-control',
            'tabindex' => '1'
        ]);
        $title->setLabel('Title');
        $title->addValidators([
            $validatorRequired,
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(CronJob::class),
                'fields' => [ 'title' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'CronJob with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Cron action
        $action = new Text('cronAction', [
            'placeholder' => 'Action command',
            'class' => 'form-control',
            'tabindex' => '2'
        ]);
        $action->setLabel('Action');
        $action->addValidators([
            $validatorRequired,
            $validatorStringLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(CronJob::class),
                'fields' => [ 'cronAction' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'CronJob with given action already exists',
                ],
            ])
        ]);
        $this->add($action);

        // Enabled
        $enabled = new Select('enabled', Enable::getLabels(), [ 'class' => 'selectpicker', 'tabindex' => '3' ]);
        $enabled->setLabel('Enabled');
        $enabled->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($enabled);

        // Cron Expression
        $cronExpr = new Text('cronExpr', [
            'placeholder' => 'Expression',
            'class' => 'form-control',
            'tabindex' => '4'
        ]);
        $cronExpr->setLabel('Expression');
        $cronExpr->addValidators([
            $validatorRequired
        ]);
        $this->add($cronExpr);

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

}
