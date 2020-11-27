<?php

namespace Users\Validator;

use Core\Exception\InvalidArgumentException;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class PasswordStrengthValidator extends Validator implements ValidatorInterface
{

    /**
     * Error message constants
     */
    const LENGTH = 'passwordStrengthInvalidLength';
    const UPPER  = 'passwordStrengthInvalidUpper';
    const LOWER  = 'passwordStrengthInvalidLower';
    const DIGIT  = 'passwordStrengthInvalidDigit';

    /**
     * Users password mint length
     */
    protected $_passwordMinLength = 8;

    /**
     * @var array Message templates
     */
    protected $_messages = [
        self::LENGTH => "Password must be at least 8 characters in length",
        self::UPPER  => "Password must contain at least one uppercase letter",
        self::LOWER  => "Password must contain at least one lowercase letter",
        self::DIGIT  => "Password must contain at least one digit character"
    ];

    /**
     * Class constructor.
     * 
     * @access public
     * @param array $options Contains parameters
     * @throws InvalidArgumentException
     * @return void
     */
    public function __construct(array $options = [])
    {
        // Init default functionality
        parent::__construct($options);

        // Set up custom messages
        if (array_key_exists('messages', $options)) {
            foreach ($options['messages'] as $key => $message) {
                $this->_messages[$key] = $message;
            }
        }

        // Set up custom min length
        if (array_key_exists('minLength', $options)) {
            if (is_int($options['minLength']) || is_numeric($options['minLength'])) {
                $this->_passwordMinLength = (int)$options['minLength'];
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid option "minLength" value given'
                ));
            }
        }
    }

    /**
     * Execute the validation
     *
     * @access public
     * @param Validation $validator
     * @param string $attribute
     * @return boolean
     */
    public function validate(Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);

        $isValid = true;

        if (strlen($value) < $this->_passwordMinLength) {
            $this->setError($validator, self::LENGTH);
            $isValid = false;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $this->setError($validator, self::UPPER);
            $isValid = false;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->setError($validator, self::LOWER);
            $isValid = false;
        }

        if (!preg_match('/\d/', $value)) {
            $this->setError($validator, self::DIGIT);
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Set error message for field.
     * 
     * @access protected
     * @param Validation $validator
     * @param string $key Error message key
     * @param string $attribute Field name
     * @return void
     */
    protected function setError(Validation $validator, $key, $attribute)
    {
        $message = isset($this->_messages[$key]) ? $this->_messages[$key] : '';
        if ($message !== '') {
            $validator->appendMessage(new Message($message, $attribute, 'PasswordStrength'));
        }
    }

}