<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use ZfcTwig\View\TwigRenderer;

class ExtensionFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return Extension
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new Extension($container->get(TwigRenderer::class));
    }
}
