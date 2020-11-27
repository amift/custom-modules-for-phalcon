<?php

namespace Translations;

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

    public function registerServices(DiInterface $di)
    {
        return [];
    }

    public function getConfig()
    {
        return \Core\Tool\ConfigLoader::getModuleConfig(__DIR__);
    }

    public function onBootstrap($application)
    {
        $di = $application->getDI();

        if (APP_TYPE === 'frontend') {
            // Init translator service
            $locale = $di->get('localeHandler')->getActualLocale();
            $di->get('translator')->init($locale);
        }
    }

}
