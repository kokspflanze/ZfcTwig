<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Twig\Loader;
use ZfcTwig\ModuleOptions;

use function is_string;

class ChainLoaderFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return Loader\ChainLoader
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @var ModuleOptions $options */
        $options = $container->get(ModuleOptions::class);

        // Setup loader
        $chain = new Loader\ChainLoader();

        foreach ($options->getLoaderChain() as $loader) {
            if (! is_string($loader) || ! $container->has($loader)) {
                throw new InvalidArgumentException('Loaders should be a service manager alias.');
            }
            $chain->addLoader($container->get($loader));
        }

        return $chain;
    }
}
