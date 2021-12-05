<?php

declare(strict_types=1);

namespace ZfcTwig\View;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TwigStrategyFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return TwigStrategy
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new TwigStrategy($container->get(TwigRenderer::class));
    }
}
