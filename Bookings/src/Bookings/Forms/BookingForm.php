<?php

namespace Bookings\Forms;

use Common\Forms\Element\DateTimeField;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\Decimal;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Bookings\Entity\Booking;
use Bookings\Tool\BookingAction;
use Bookings\Tool\BookingType;

class BookingForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Booking $entity
     * @param array $options
     * @return void
     */
    public function initialize(Booking $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorMaxStringLength = new StringLength([
            'max' => 255,
            'messageMaximum' => "Max 255 chars allowed",
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Type
        $type = new Select('type', BookingType::getLabels(), [ 'class' => 'selectpicker' ]);
        $type->setLabel('Type');
        $type->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => BookingType::getTypes()
            ])
        ]);
        $this->add($type);

        // Action
        $action = new Select('action', BookingAction::getLabelsAsOptGroup(), [ 'class' => 'selectpicker' ]);
        $action->setLabel('Action');
        $action->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => BookingAction::getActions()
            ])
        ]);
        $this->add($action);

        // Date
        $date = $entity->getDate();
        if (!is_object($date)) {
            $date = new \DateTime('now');
        }
        $entity->setDate($date->format('d/m/Y H:i:s'));
        $publicationDate = new DateTimeField('date', [ 'class' => 'form-control' ]);
        $publicationDate->setLabel('Date');
        $publicationDate->setFieldId('date');
        $publicationDate->addValidators([
            $validatorRequired
        ]);
        $this->add($publicationDate);

        // Amount
        $amount = new Numeric('amount', [ 'class' => 'form-control' ]);
        $amount->setLabel('Amount');
        $amount->addValidators([
            $validatorRequired
        ]);
        $this->add($amount);

        // Currency
        $currency = new Text('currency', [ 'class' => 'form-control' ]);
        $currency->setLabel('Currency');
        //$currency->setDefault('USD');
        $currency->addValidators([
            $validatorRequired
        ]);
        $this->add($currency);

        // Comment
        $comment = new TextArea('comment', [ 'class' => 'form-control', 'style' => 'height: 100px;' ]);
        $comment->setLabel('Comment');
        $comment->addValidators([
            $validatorMaxStringLength
        ]);
        $this->add($comment);

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
