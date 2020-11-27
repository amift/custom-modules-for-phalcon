<?php

namespace Application;

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
        return \Core\Tool\ConfigLoader::getModuleConfig(__DIR__);
    }

    public function registerServices(DiInterface $di)
    {
        //
    }

    /*public function onBootstrap($application)
    {
        $di = $application->getDI();
        //$eventsManager = $application->getEventsManager();
    }*/

}
