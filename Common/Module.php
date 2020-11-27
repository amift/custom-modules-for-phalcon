<?php

namespace Common;

use Core\Tool\ConfigLoader;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    public function registerAutoloaders(DiInterface $di = NULL)
    {
        $loader = new Loader();
        $loader->registerNamespaces([
            __NAMESPACE__ => __DIR__ . DS . 'src' . DS . __NAMESPACE__,
        ]);
        $loader->register();
    }

    public function getConfig()
    {
        return ConfigLoader::getModuleConfig(__DIR__);
    }

    public function registerServices(DiInterface $di)
    {
        //
    }

    public function onBootstrap($application)
    {
        $di = $application->getDI();
        
        if (APP_TYPE === 'frontend') {
            // Init localeHandler service
            $di->get('localeHandler')->init();
            // Init meta data service
            $di->get('metaData')->init();

            /*echo 'getLocalesList';
            var_dump($di->get('localeHandler')->getLocalesList());
            echo 'getLocalesValues';
            var_dump($di->get('localeHandler')->getLocalesValues());
            echo 'getActualLocale';
            var_dump($di->get('localeHandler')->getActualLocale());
            die();*/
        }
    }

}
