<?php

use Twig\Environment;
use Laminas\Navigation\View\HelperConfig;
use ZfcTwig\Twig;
use ZfcTwig\View;

return [
    'zfctwig' => [
        /**
         * Service manager alias of the loader to use with ZfcTwig. By default, it uses
         * the included ZfcTwigLoaderChain which includes a copy of ZF2's TemplateMap and
         * TemplatePathStack.
         */
        'environment_loader' => 'ZfcTwigLoaderChain',

        /**
         * Optional class name override for instantiating the Twig Environment in the factory.
         */
        'environment_class' => Environment::class,

        /**
         * Options that are passed directly to the Twig_Environment.
         */
        'environment_options' => [],

        /**
         * Service manager alias of any additional loaders to register with the chain. The default
         * has the TemplateMap and TemplatePathStack registered. This setting only has an effect
         * if the `environment_loader` key above is set to ZfcTwigLoaderChain.
         */
        'loader_chain' => [
            Twig\MapLoader::class,
            Twig\StackLoader::class
        ],

        /**
         * Service manager alias or fully qualified domain name of extensions. ZfcTwigExtension
         * is required for this module to function!
         */
        'extensions' => [
            'zfctwig' => Twig\Extension::class
        ],

        /**
         * The suffix of Twig files. Technically, Twig can load *any* type of file
         * but the templates in ZF are suffix agnostic so we must specify the extension
         * that's expected here.
         */
        'suffix' => 'twig',

        /**
         * When enabled, the ZF2 view helpers will get pulled using a fallback renderer. This will
         * slightly degrade performance but must be used if you plan on using any of ZF2's view helpers.
         */
        'enable_fallback_functions' => true,

        /**
         * If set to true disables ZF's notion of parent/child layouts in favor of
         * Twig's inheritance model.
         */
        'disable_zf_model' => true,

        /**
         * ZfcTwig uses it's own HelperPluginManager to avoid renderer conflicts with the PhpRenderer. You must register
         * any view helpers in this array that require access to the renderer. The defaults from ZF2 (navigation,
         * partial, etc.) are done for you.
         */
        'helper_manager' => [
            'configs' => [
                HelperConfig::class
            ]
        ]
    ],

    /**
     * Load services.
     */
    'service_manager' => include __DIR__ . '/service.config.php',

    /**
     * Register the view strategy with the view manager. This is required!
     */
    'view_manager' => [
        'strategies' => [View\TwigStrategy::class]
    ]
];
