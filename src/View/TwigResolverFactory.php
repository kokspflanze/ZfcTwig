<?php

declare(strict_types=1);

namespace ZfcTwig\View;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Twig\Environment;

class TwigResolverFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return TwigResolver
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new TwigResolver($container->get(Environment::class));
    }
}
