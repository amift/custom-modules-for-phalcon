<?php

namespace Members\Forms;

use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;

class LoginForm extends Form
{

    /**
     * Form initialize
     * 
     * @access public
     * @return void
     */
    public function initialize()
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Lauks ir obligāts'
        ]);

        // Email
        $email = new Text('login_email', [
            'placeholder' => 'E-pasts',
            'tabindex' => '1'
        ]);
        $email->setLabel('E-pasts');
        $email->addValidators([
            $validatorRequired,
            new Email([
                'message' => 'E-pasta adrese nav korekta'
            ])
        ]);
        $this->add($email);

        // Password
        $password = new Password('login_password', [
            'placeholder' => 'Parole',
            'tabindex' => '2'
        ]);
        $password->setLabel('Parole');
        $password->addValidators([
            $validatorRequired
        ]);
        $password->clear();
        $this->add($password);

        /*// CSRF
        $csrf = new Hidden('login_csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);*/

        parent::initialize();
    }

}
