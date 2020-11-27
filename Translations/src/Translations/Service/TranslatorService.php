<?php

namespace Translations\Service;

use Core\Library\AbstractLibrary;
use Translations\Entity\Translation;
use Translations\Entity\TranslationValue;
use Translations\Tool\Group;

class TranslatorService extends AbstractLibrary
{

    /**
     * @var string
     */
    private $_locale;

    /**
     * @var string
     */
    private $_group;

    /**
     * @var array
     */
    private $_translations;

    /**
     * @var \Translations\Repository\TranslationRepository
     */
    protected $_translationRepo;

    /**
     * Init translator.
     * 
     * @access public
     * @param string $locale
     * @throws \Core\Exception\InvalidArgumentException
     */
    public function init($locale = null)
    {
        $this->setLocale($locale);

        if ($this->getLocale() === null) {
            throw new \Core\Exception\InvalidArgumentException('Missing locale parameter value');
        }
    }

    /**
     * Set locale
     * 
     * @access public
     * @param string $locale
     * @return void
     */
    public function setLocale($locale = null)
    {
        $this->_locale = $locale;
    }

    /**
     * Get locale
     *
     * @access public
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Set group
     * 
     * @access public
     * @param string $group
     * @return void
     */
    public function setGroup($group = null)
    {
        $this->_group = $group;
    }

    /**
     * Get group
     *
     * @access public
     * @return string
     */
    public function getGroup()
    {
        return $this->_group !== null ? $this->_group : Group::NOT_SET;
    }

    /**
     * Unset previously setted group
     * 
     * @access public
     * @return void
     */
    public function unsetGroup()
    {
        $this->setGroup(null);
    }

    /**
     * Get translated value.
     * 
     * @access public
     * @param string $key
     * @param string $defaultValue
     * @param null|string $group
     * @return string
     * @throws \Core\Exception\InvalidArgumentException
     */
    public function trans($key = null, $defaultValue = null, $group = null)
    {
        if ($this->_translations === null) {
            $this->_translations = $this->loadTranslations();
        }

        if ($this->getLocale() === null) {
            throw new \Core\Exception\InvalidArgumentException('Missing locale parameter value');
        }

        if ($group === null) {
            $group = $this->getGroup();
        }

        if (! isset($this->_translations[$group][$key])) {
            $defaultValue = ! empty($defaultValue) ? $defaultValue : $key;
            $this->_translations[$group][$key] = $defaultValue;
            $this->createMessage($key, $group, $defaultValue);
        }

        return $this->_translations[$group][$key];
    }

    /**
     * Load translations from DB.
     * Returnend array structure
     * array(
     *    'group1' => array(
     *        'message_key1' => 'This is message 1',
     *        'message_key2' => 'This is message 2',
     *        ...
     *     ),
     *     ...
     * )
     * 
     * @toDo - ADD CACHING MECHANISM
     * 
     * @access protected
     * @return array
     */
    protected function loadTranslations()
    {
        $values = [];

        $locale = $this->getLocale();

        foreach ($this->getTranslationRepo()->getMessages($locale) as $row) {

            if (! array_key_exists($row['group'], $values)) {
                $values[$row['group']] = [];
            }

            $value = $row['value'] !== '' ? $row['value'] : $row['defaultValue'];

            $values[$row['group']][$row['key']] = $value;
        }

        //var_dump($values);
        //die();
        return $values;
    }

    /**
     * Create new message
     * 
     * @access protected
     * @param string $key
     * @param string $group
     * @param string $defaultValue
     * @return void
     */
    protected function createMessage($key, $group, $defaultValue)
    {
        $message = new Translation($key, $group, $defaultValue);

        foreach ($this->di->get('localeHandler')->getLocalesValues() as $locale) {
            $message->addValue(new TranslationValue($locale, $defaultValue));
        }

        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush($message);
    }

    /**
     * Get Translation entity repository
     * 
     * @access protected
     * @return \Translations\Repository\TranslationRepository
     */
    protected function getTranslationRepo()
    {
        if ($this->_translationRepo === null || !$this->_translationRepo) {
            $this->_translationRepo = $this->getEntityRepository(Translation::class);
        }

        return $this->_translationRepo;
    }

}
