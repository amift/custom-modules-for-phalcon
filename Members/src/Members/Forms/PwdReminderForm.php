<?php

namespace Members\Forms;

use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;

class PwdReminderForm extends Form
{

    /**
     * Form initialize
     * 
     * @access public
     * @return void
     */
    public function initialize()
    {
        // Email
        $email = new Text('reminder_email', [
            'placeholder' => 'E-pasts',
            'tabindex' => '1'
        ]);
        $email->setLabel('E-pasts');
        $email->addValidators([
            new PresenceOf([
                'message' => 'Lauks ir obligāts'
            ]),
            new Email([
                'message' => 'E-pasta adrese nav korekta'
            ])
        ]);
        $this->add($email);

        /*// CSRF
        $csrf = new Hidden('reminder_csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);*/

        parent::initialize();
    }

}
