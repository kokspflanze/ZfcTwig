<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Twig\Environment;
use Twig\TwigFunction;
use ZfcTwig\ModuleOptions;

use function sprintf;

class EnvironmentFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     * @return Environment
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @var ModuleOptions $options */
        $options  = $container->get(ModuleOptions::class);
        $envClass = $options->getEnvironmentClass();

        if (! $container->has($options->getEnvironmentLoader())) {
            throw new RuntimeException(
                sprintf(
                    'Loader with alias "%s" could not be found!',
                    $options->getEnvironmentLoader()
                )
            );
        }

        /** @var Environment $env */
        $env = new $envClass($container->get($options->getEnvironmentLoader()), $options->getEnvironmentOptions());

        if ($options->getEnableFallbackFunctions()) {
            $helperPluginManager = $container->get('ViewHelperManager');
            $env->registerUndefinedFunctionCallback(
                function ($name) use ($helperPluginManager) {
                    if ($helperPluginManager->has($name)) {
                        return new TwigFunction(
                            $name,
                            sprintf(
                                '$this->env->getExtension("%s")->getRenderer()->plugin("%s")->__invoke',
                                Extension::class,
                                $name
                            ),
                            ['is_safe' => ['all']]
                        );
                    }
                    return false;
                }
            );
        }

        foreach ($options->getGlobals() as $name => $value) {
            $env->addGlobal($name, $value);
        }

        // Extensions are loaded later to avoid circular dependencies (for example, if an extension needs Renderer).
        return $env;
    }
}
