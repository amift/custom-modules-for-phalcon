<?php

namespace Common\Service;

use Core\Library\AbstractLibrary;

class LocaleHandlerService extends AbstractLibrary
{

    /**
     * @var string
     */
    protected $_sessionKey = 'locale-%s';

    /**
     * Init locale handler
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        $locale = $this->hasLocale() ? $this->getLocale() : $this->getDefaultLocale();
        $this->setLocale($locale);
    }

    /**
     * Get default locale from config
     * 
     * @access public
     * @return string
     */
    public function getDefaultLocale()
    {
        return \Phalcon\Di::getDefault()->get('config')->translations->default_locale;
        //return $this->di->getConfig()->translations->default_locale;
    }

    /**
     * Get actual locale from session
     * 
     * @access public
     * @return string
     */
    public function getActualLocale()
    {
        return 'lv';/*
        if (!$this->hasLocale()) {
            $this->setLocale($this->getDefaultLocale());
        }

        return $this->getLocale();*/
    }
    
    public function getLocalesList()
    {
        return [ 'lv' => 'LV' ];/*
        return (array)\Phalcon\Di::getDefault()->get('config')->translations->locales;
        //return (array)$this->di->getConfig()->translations->locales;*/
    }

    public function getLocalesValues()
    {
        return [ 'lv' => 'LV' ];/*
        return array_keys((array)\Phalcon\Di::getDefault()->get('config')->translations->locales);
        //return array_keys((array)$this->di->getConfig()->translations->locales);*/
    }

    /**
     * Set locale to session
     * 
     * @access public
     * @param string $locale
     * @return void
     */
    public function setLocale($locale = null)
    {
        //\Phalcon\Di::getDefault()->get('session')->set($this->getSessionKey(), $locale);
        //$this->di->getSession()->set($this->getSessionKey(), $locale);
    }

    /**
     * Get locale from session
     *
     * @access public
     * @return string
     */
    public function getLocale()
    {
        return 'lv';/*
        return \Phalcon\Di::getDefault()->get('session')->get($this->getSessionKey());
        //return $this->di->getSession()->get($this->getSessionKey());*/
    }

    /**
     * Check if has valid locale in session
     * 
     * @access public
     * @return boolean
     */
    public function hasLocale()
    {
        return true;/*
        $locale = null;

        //if ($this->di->getSession()->has($this->getSessionKey())) {
        if (\Phalcon\Di::getDefault()->get('session')->has($this->getSessionKey())) {
            $locale = $this->getLocale();
        }

        if ($locale !== null && $locale !== '') {
            return true;
        }

        return false;*/
    }

    /**
     * Get locale session key
     * 
     * @access public
     * @return string
     */
    public function getSessionKey()
    {
        return sprintf($this->_sessionKey, APP_TYPE);
    }

}
