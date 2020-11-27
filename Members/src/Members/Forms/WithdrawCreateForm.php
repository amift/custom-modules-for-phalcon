<?php

namespace Members\Forms;

use Common\Forms\Validator\RequiredWhenFieldValueValidator;
use Core\Forms\Form;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Members\Entity\Withdraws;
use Members\Tool\WithdrawType;

class WithdrawCreateForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @return void
     */
    public function initialize(Withdraws $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Lauks ir obligāts'
        ]);
        
        $validatorRequiredWhenBankTransfer = new RequiredWhenFieldValueValidator([
            'field_name' => 'type',
            'field_value' => WithdrawType::TYPE_BANK_TRANSFER,
            'messages' => [
                RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Lauks ir obligāts',
            ],
        ]);

        // Type
        $type = new Select('type', WithdrawType::getFrontendLabels(), [
            'tabindex' => '1'
        ]);
        $type->setLabel('Izmaksas veids');
        $type->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined type given',
                'domain' => WithdrawType::getTypes()
            ])
        ]);
        $this->add($type);

        // PayPal Email
        $paypalEmail = new Text('paypalEmail', [
            'tabindex' => '2'
        ]);
        $paypalEmail->setLabel('PayPal E-pasts');
        $paypalEmail->addValidators([
            new RequiredWhenFieldValueValidator([
                'field_name' => 'type',
                'field_value' => WithdrawType::TYPE_PAYPAL,
                'messages' => [
                    RequiredWhenFieldValueValidator::ERROR_REQUIRED => 'Lauks ir obligāts',
                ],
            ])
        ]);
        $this->add($paypalEmail);

        // Bank Name
        $bankName = new Text('bankName', [
            'tabindex' => '3'
        ]);
        $bankName->setLabel('Bankas nosaukums');
        $bankName->addValidators([
            $validatorRequiredWhenBankTransfer
        ]);
        $this->add($bankName);

        // Bank Account
        $bankAccount = new Text('bankAccount', [
            'tabindex' => '4'
        ]);
        $bankAccount->setLabel('Konta nr.');
        $bankAccount->addValidators([
            $validatorRequiredWhenBankTransfer
        ]);
        $this->add($bankAccount);

        // Receiver Name
        $receiverName = new Text('receiverName', [
            'tabindex' => '5'
        ]);
        $receiverName->setLabel('Vārds Uzvārds');
        $receiverName->addValidators([
            $validatorRequiredWhenBankTransfer/*,
            new StringLength([
                'max' => 250,
                'min' => 5,
                'messageMaximum' => "Max 200 simboli atļauti",
                'messageMinimum' => "Vismaz 5 simboli jāievada"
            ])*/
        ]);
        $this->add($receiverName);

        // Pts
        $pts = new Text('pts', [
            'tabindex' => '6'
        ]);
        $pts->setLabel('Apmainīt punktus');
        $pts->addValidators([
            $validatorRequired,
            new DigitValidator([
                'message' => 'Jāievada skaitlis'
            ])
        ]);
        $this->add($pts);

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
