<?php

namespace ZfcTwig;

use InvalidArgumentException;
use Twig\Environment;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use function is_string;
use function is_object;

class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface
{
    /**
     * @param EventInterface $e
     * @throws InvalidArgumentException
     */
    public function onBootstrap(EventInterface $e): void
    {
        /** @var \Laminas\Mvc\MvcEvent $e*/
        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $environment    = $serviceManager->get(Environment::class);

        /** @var ModuleOptions $options */
        $options = $serviceManager->get(ModuleOptions::class);

        // Setup extensions
        foreach ($options->getExtensions() as $extension) {
            // Allows modules to override/remove extensions.
            if (empty($extension)) {
                continue;
            } elseif (is_string($extension)) {
                if ($serviceManager->has($extension)) {
                    $extension = $serviceManager->get($extension);
                } else {
                    $extension = new $extension();
                }
            } elseif (!is_object($extension)) {
                throw new InvalidArgumentException('Extensions should be a string or object.');
            }

            $environment->addExtension($extension);
        }
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

}
