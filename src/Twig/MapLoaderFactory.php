<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Twig\Error\LoaderError;
use ZfcTwig\ModuleOptions;

use function pathinfo;

use const PATHINFO_EXTENSION;

class MapLoaderFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return MapLoader
     * @throws LoaderError
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @var ModuleOptions $options */
        $options = $container->get(ModuleOptions::class);

        /** @var TemplateMapResolver $zfTemplateMap */
        $zfTemplateMap = $container->get('ViewTemplateMapResolver');

        $templateMap = new MapLoader();
        foreach ($zfTemplateMap as $name => $path) {
            if ($options->getSuffix() === pathinfo($path, PATHINFO_EXTENSION)) {
                $templateMap->add($name, $path);
            }
        }

        return $templateMap;
    }
}
