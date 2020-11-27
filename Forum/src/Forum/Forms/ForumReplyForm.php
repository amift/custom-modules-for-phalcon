<?php

namespace Forum\Forms;

use Common\Tool\Enable;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\PresenceOf;
use Forum\Entity\ForumReply;

class ForumReplyForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param ForumReply $entity
     * @param array $options
     * @return void
     */
    public function initialize(ForumReply $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Blocked
        $blocked = new Select('blocked', Enable::getShortLabels(), [ 'class' => 'selectpicker', 'tabindex' => '1' ]);
        $blocked->setLabel('Blocked');
        $blocked->addValidators([
            $validatorRequired,
        ]);
        $this->add($blocked);

        // Content
        $content = new TextArea('content', [ 'class' => 'form-control', 'tabindex' => '2', 'style' => 'height: 150px;' ]);
        $content->setLabel('Comment');
        $content->addValidators([
            $validatorRequired,
        ]);
        $this->add($content);

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
