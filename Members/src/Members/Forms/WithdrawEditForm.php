<?php

namespace Members\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Forms\Element\DateTimeField;
use Common\Forms\Validator\RequiredWhenFieldValueValidator;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Members\Entity\Withdraws;
use Members\Tool\WithdrawState;

class WithdrawEditForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Withdraws $entity
     * @param array $options
     * @return void
     */
    public function initialize(Withdraws $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Status
        $state = new Select('state', WithdrawState::getLabels(), [ 'class' => 'selectpicker', 'tabindex' => '1' ]);
        $state->setLabel('Status');
        $state->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined group given',
                'domain' => WithdrawState::getStates()
            ])
        ]);
        $this->add($state);

        // Transaction Number
        $transactionNumber = new Text('transactionNumber', [ 'class' => 'form-control', 'tabindex' => '2' ]);
        $transactionNumber->setLabel('Transaction Number');
        $transactionNumber->addValidators([
            new RequiredWhenFieldValueValidator([
                'field_name' => 'state',
                'field_value' => WithdrawState::STATE_COMPLETED,
                'messages' => [
                    RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Transaction number is required',
                ],
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Withdraws::class),
                'fields' => [ 'transactionNumber' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Withdraw with given transaction number already exists',
                ],
            ])
        ]);
        $this->add($transactionNumber);

        // Publication date
        $transactionDate = new DateTimeField('transactionDate', [ 'class' => 'form-control', 'tabindex' => '3' ]);
        $transactionDate->setLabel('Transaction Date');
        $transactionDate->setFieldId('transactionDate');
        $transactionDate->addValidators([
            new RequiredWhenFieldValueValidator([
                'field_name' => 'state',
                'field_value' => WithdrawState::STATE_COMPLETED,
                'messages' => [
                    RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Transaction date is required',
                ],
            ])
        ]);
        $this->add($transactionDate);

        // Reason
        $reason = new Text('reason', [ 'class' => 'form-control', 'tabindex' => '4' ]);
        $reason->setLabel('Cancelation reason');
        $reason->addValidators([
            new RequiredWhenFieldValueValidator([
                'field_name' => 'state',
                'field_value' => WithdrawState::STATE_REJECTED,
                'messages' => [
                    RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Cancelation reason is required',
                ],
            ])
        ]);
        $this->add($reason);

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
