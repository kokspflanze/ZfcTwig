<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Resolver\TemplatePathStack;
use Psr\Container\ContainerInterface;
use ZfcTwig\ModuleOptions;

class StackLoaderFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return StackLoader
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @var ModuleOptions $options */
        $options = $container->get(ModuleOptions::class);

        /** @var TemplatePathStack $zfTemplateStack */
        $zfTemplateStack = $container->get('ViewTemplatePathStack');

        $templateStack = new StackLoader($zfTemplateStack->getPaths()->toArray());
        $templateStack->setDefaultSuffix($options->getSuffix());

        return $templateStack;
    }
}
