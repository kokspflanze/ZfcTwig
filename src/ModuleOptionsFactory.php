<?php

declare(strict_types=1);

namespace ZfcTwig;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return ModuleOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');

        return new ModuleOptions($config['zfctwig'] ?? []);
    }
}
