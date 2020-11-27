<?php

namespace Communication\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Tool\Enable;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Communication\Entity\Template;
use Communication\Tool\TemplateModule;
use Communication\Tool\TemplateType;

class TemplateForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Template $entity
     * @param array $options
     * @return void
     */
    public function initialize(Template $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorNamesLength = new StringLength([
            'max' => 255,
            'min' => 2,
            'messageMaximum' => "Max 255 chars allowed",
            'messageMinimum' => "At least 2 chars need"
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Enabled
        $enabled = new Select('enabled', Enable::getLabels(), [ 'class' => 'selectpicker', 'tabindex' => '1' ]);
        $enabled->setLabel('Enabled');
        $enabled->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($enabled);

        // Title
        $title = new Text('title', [ 'class' => 'form-control', 'tabindex' => '2', 'placeholder' => 'Title' ]);
        $title->setLabel('Title');
        $title->addValidators([
            $validatorRequired,
            $validatorNamesLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Template::class),
                'fields' => [ 'title' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Template with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Description
        $description = new TextArea('description', [
            'class' => 'form-control',
            'tabindex' => '3',
            'style' => 'height: 100px;'
        ]);
        $description->setLabel('Description');
        $description->addValidators([
            new StringLength([
                'max' => 255,
                'messageMaximum' => "Max 255 chars allowed",
            ])
        ]);
        $this->add($description);

        // Sender title
        $fromName = new Text('fromName', [ 'class' => 'form-control', 'tabindex' => '4', 'placeholder' => 'Sender name' ]);
        $fromName->setLabel('Sender name');
        $fromName->addValidators([
            $validatorRequired,
            $validatorNamesLength
        ]);
        $this->add($fromName);

        // Sender e-mail
        $fromEmail = new Text('fromEmail', [ 'class' => 'form-control', 'tabindex' => '5', 'placeholder' => 'Sender e-mail' ]);
        $fromEmail->setLabel('Sender e-mail');
        $fromEmail->addValidators([
            $validatorRequired,
            new Email([
                'message' => 'E-mail address is invalid'
            ]),
        ]);
        $this->add($fromEmail);

        // Subject
        $subject = new Text('subject', [ 'class' => 'form-control', 'tabindex' => '6', 'placeholder' => 'Message subject' ]);
        $subject->setLabel('Subject');
        $subject->addValidators([
            $validatorRequired,
            $validatorNamesLength
        ]);
        $this->add($subject);

        // Body
        $body = new TextArea('body', [
            'class' => 'form-control',
            'tabindex' => '7',
            'style' => 'height: 350px;'
        ]);
        $body->setLabel('Content');
        $body->addValidators([
            $validatorRequired
        ]);
        $this->add($body);

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
