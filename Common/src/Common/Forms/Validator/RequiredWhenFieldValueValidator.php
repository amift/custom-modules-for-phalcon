<?php

namespace Common\Forms\Validator;

use Core\Exception\InvalidArgumentException;
use Core\Exception\RuntimeException;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class RequiredWhenFieldValueValidator extends Validator implements ValidatorInterface
{

    /**
     * Error constants
     */
    const ERROR_REQUIRED = 'requiredWhenFieldValue';

    /**
     * @var array Message templates
     */
    protected $_messages = [
        self::ERROR_REQUIRED => "Value is required",
    ];

    /**
     * @var string
     */
    protected $_field_name;

    /**
     * @var string
     */
    protected $_field_value;

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

        // Set up field name to check
        if (array_key_exists('field_name', $options)) {
            $this->_field_name = trim((string)$options['field_name']);
        }
        if (!is_string($this->_field_name) || strlen($this->_field_name) < 1) {
            throw new InvalidArgumentException(sprintf(
                'Option "field_name" has no defined value'
            ));
        }

        // Set up field value to check
        if (array_key_exists('field_value', $options)) {
            $this->_field_value = trim((string)$options['field_value']);
        }
        if (!is_string($this->_field_value) || strlen($this->_field_value) < 1) {
            throw new InvalidArgumentException(sprintf(
                'Option "field_value" has no defined value'
            ));
        }
    }

    public function validate(Validation $validator, $attribute)
    {
        // Get current field value
        $currentValue = $validator->getValue($attribute);

        // Get defined field value by given field name
        $value = (string)$validator->getValue($this->_field_name);

        // Check if required field value is same as defined
        if ($value !== null && $value === $this->_field_value) {
            if ($currentValue === null || $currentValue === '') {
                $this->setError($validator, self::ERROR_REQUIRED);
                return false;
            }
        }

        return true;
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
            $validator->appendMessage(new Message($message, $attribute, 'RequiredWhenFieldValue'));
        }
    }

}
