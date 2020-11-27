<?php

namespace Common\Doctrine\Validator;

use Core\Exception\InvalidArgumentException;
use Core\Exception\RuntimeException;
use Doctrine\Common\Persistence\ObjectManager;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class UniqueObjectValidator extends Validator implements ValidatorInterface
{

    /**
     * Error constants
     */
    const ERROR_OBJECT_NOT_UNIQUE = 'objectNotUnique';

    /**
     * @var array Message templates
     */
    protected $_messages = [
        self::ERROR_OBJECT_NOT_UNIQUE => "There is already another object matching '%value%'",
    ];

    /**
     * @var array
     */
    protected $_fields;

    /**
     * @var mixed
     */
    protected $_objectRepository;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var boolean
     */
    protected $_simulateIdentifier;

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

        // Set up fields
        if (array_key_exists('fields', $options)) {
            $this->_fields = $options['fields'];
        }
        if (!is_array($this->_fields) || count($this->_fields) < 1) {
            throw new InvalidArgumentException(sprintf(
                'Option "fields" has no defined object properties to check. At least one field must be defined'
            ));
        }

        // Set up object repository
        if (array_key_exists('object_repository', $options)) {
            $this->_objectRepository = $options['object_repository'];
        }
        if (!is_object($this->_objectRepository) || !method_exists($this->_objectRepository, 'findOneBy')) {
            throw new InvalidArgumentException(sprintf(
                'Option "object_repository" is not valid entity repository'
            ));
        }

        // Set up object manager
        if (array_key_exists('object_manager', $options)) {
            $this->_objectManager = $options['object_manager'];
        }
        if (!$this->_objectManager instanceof ObjectManager) {
            throw new InvalidArgumentException(sprintf(
                'Option "object_manager" is required and must be an instance of "Doctrine\Common\Persistence\ObjectManager"'
            ));
        }

        // Set up custom funcationlity when no ID fields in form used
        $this->_simulateIdentifier = false;
        if (array_key_exists('simulate_identifier', $options)) {
            $this->_simulateIdentifier = (boolean)$options['simulate_identifier'];
        }
    }

    /**
     * Execute the validation.
     *
     * @access public
     * @param Validation $validator
     * @param string $attribute
     * @return boolean
     */
    public function validate(Validation $validator, $attribute)
    {
        // Get defined field values to set up for searching
        $values = [];
        foreach ($this->_fields as $contextKey) {
            $value = $validator->getValue($contextKey);
            //if ($value !== null) {
                $values[$contextKey] = $value;
            //}
        }

        // Validate checking fields
        if (count($values) < 1 || count($values) !== count($this->_fields)) {
            throw new RuntimeException(sprintf(
                'Invalid provided fields count, expected fields to be matched %s "%s"',
                count($this->_fields) > 1 ? 'are' : 'is',
                implode('", "', $this->_fields)
            ));
        }

        // Search for object
        $match = $this->_objectRepository->findOneBy($values);

        // Object not exits, so it is unique
        if (!is_object($match)) {
            return true;
        }

        // Check object identifiers (if same object editing than must allow to use same value)
        $expectedIdentifiers = $this->getExpectedIdentifiers($validator);
        $foundIdentifiers    = $this->getFoundIdentifiers($match);
        if (count(array_diff_assoc($expectedIdentifiers, $foundIdentifiers)) == 0) {
            return true;
        }

        // Set up error
        $this->setError($validator, self::ERROR_OBJECT_NOT_UNIQUE);
        return false;
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
            $validator->appendMessage(new Message($message, $attribute, 'UniqueObject'));
        }
    }

    /**
     * Gets the identifiers from the context.
     *
     * @access protected
     * @param Validation $validator
     * @return array
     * @throws RuntimeException
     */
    protected function getExpectedIdentifiers(Validation $validator)
    {
        $result = [];

        foreach ($this->getIdentifiers() as $identifierField) {
            $value = $validator->getValue($identifierField);
            if ($value === null) {
                if ($this->_simulateIdentifier === true) {
                    $value = 0;
                } else {
                    throw new RuntimeException(sprintf('Expected to contain %s', $identifierField));
                }
            }

            $result[$identifierField] = $value;
        }

        return $result;
    }

    /**
     * Gets the default identifiers.
     * 
     * @access protected
     * @return array the names of the identifiers
     */
    protected function getIdentifiers()
    {
        return $this->_objectManager
                    ->getClassMetadata($this->_objectRepository->getClassName())
                    ->getIdentifierFieldNames();
    }

    /**
     * Gets the identifiers from the matched object.
     *
     * @access protected
     * @param object $match
     * @return array
     */
    protected function getFoundIdentifiers($match)
    {
        return $this->_objectManager
                    ->getClassMetadata($this->_objectRepository->getClassName())
                    ->getIdentifierValues($match);
    }

}