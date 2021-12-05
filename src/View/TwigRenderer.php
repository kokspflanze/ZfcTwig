<?php

declare(strict_types=1);

namespace ZfcTwig\View;

use ArrayAccess;
use Laminas\View\Exception;
use Laminas\View\Exception\DomainException;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Renderer\TreeRendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\View;
use Twig\Environment;
use Twig\Loader;
use Twig\Template;

use function call_user_func_array;
use function is_callable;
use function sprintf;

class TwigRenderer implements RendererInterface, TreeRendererInterface
{
    /** @var bool */
    protected $canRenderTrees = true;

    /** @var Environment */
    protected $environment;

    /** @var HelperPluginManager */
    protected $helperPluginManager;

    /** @var HelperPluginManager */
    protected $zendHelperPluginManager;

    /** @var Loader\ChainLoader */
    protected $loader;

    /** @var TwigResolver */
    protected $resolver;

    /** @var View */
    protected $view;

    /** @var array Cache for the plugin call */
    private $pluginCache = [];

    public function __construct(
        View $view,
        Loader\ChainLoader $loader,
        Environment $environment,
        TwigResolver $resolver
    ) {
        $this->environment = $environment;
        $this->loader      = $loader;
        $this->resolver    = $resolver;
        $this->view        = $view;
    }

    /**
     * Overloading: proxy to helpers
     *
     * Proxies to the attached plugin manager to retrieve, return, and potentially
     * execute helpers.
     *
     * * If the helper does not define __invoke, it will be returned
     * * If the helper does define __invoke, it will be called as a functor
     *
     * @param  array $argv
     * @return mixed
     */
    public function __call(string $method, array $argv)
    {
        if (! isset($this->pluginCache[$method])) {
            $this->pluginCache[$method] = $this->plugin($method);
        }

        if (is_callable($this->pluginCache[$method])) {
            return call_user_func_array($this->pluginCache[$method], $argv);
        }

        return $this->pluginCache[$method];
    }

    /**
     * @return $this
     */
    public function setCanRenderTrees(bool $canRenderTrees): self
    {
        $this->canRenderTrees = $canRenderTrees;
        return $this;
    }

    public function canRenderTrees(): bool
    {
        return $this->canRenderTrees;
    }

    /**
     * Get plugin instance, proxy to HelperPluginManager::get
     *
     * @param  string     $name Name of plugin to return
     * @param  null|array $options Options to pass to plugin constructor (if not already instantiated)
     * @return AbstractHelper
     */
    public function plugin(string $name, ?array $options = null)
    {
        $helper = $this->getHelperPluginManager()
                    ->setRenderer($this);

        if ($helper->has($name)) {
            return $helper->get($name, $options);
        }

        return $this->getZendHelperPluginManager()->get($name, $options);
    }

    /**
     * Can the template be rendered?
     *
     * @see \ZfcTwig\Twig\Environment::canLoadTemplate()
     */
    public function canRender(string $name): bool
    {
        return $this->loader->exists($name);
    }

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     */
    public function getEngine(): Environment
    {
        return $this->environment;
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @return $this
     */
    public function setResolver(ResolverInterface $resolver): self
    {
        $this->resolver = $resolver;
        return $this;
    }

    /**
     * @return $this
     */
    public function setHelperPluginManager(HelperPluginManager $helperPluginManager): self
    {
        $helperPluginManager->setRenderer($this);
        $this->helperPluginManager = $helperPluginManager;
        return $this;
    }

    public function getHelperPluginManager(): HelperPluginManager
    {
        return $this->helperPluginManager;
    }

    public function getZendHelperPluginManager(): HelperPluginManager
    {
        return $this->zendHelperPluginManager;
    }

    /**
     * @return $this
     */
    public function setZendHelperPluginManager(HelperPluginManager $zendHelperPluginManager): self
    {
        $this->zendHelperPluginManager = $zendHelperPluginManager;
        return $this;
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param  string|ModelInterface   $nameOrModel The script/resource process, or a view model
     * @param null|array|ArrayAccess $values Values to use during rendering
     * @return string|null The script output.
     * @throws DomainException
     */
    public function render($nameOrModel, $values = null): ?string
    {
        $model = null;

        if ($nameOrModel instanceof ModelInterface) {
            $model       = $nameOrModel;
            $nameOrModel = $model->getTemplate();

            if (empty($nameOrModel)) {
                throw new Exception\DomainException(sprintf(
                    '%s: received View Model argument, but template is empty',
                    __METHOD__
                ));
            }

            $values = (array) $model->getVariables();
        }

        if (! $this->canRender($nameOrModel)) {
            return null;
        }

        if (null === $values) {
            $values = [];
        }

        if ($model && $this->canRenderTrees() && $model->hasChildren()) {
            $values['content'] = $values['content'] ?? '';
            foreach ($model as $child) {
                /** @var ModelInterface $child */
                if ($this->canRender($child->getTemplate())) {
                    $template = $this->resolver->resolve($child->getTemplate(), $this);

                    $childValues = (array) $child->getVariables();
                    if ($child->hasChildren()) {
                        foreach ($child->getChildren() as $grandChild) {
                            /** @var ModelInterface $grandChild */
                            $childValues[$grandChild->captureTo()] = $this->render($grandChild);
                        }
                    }

                    return $template->render($childValues);
                }
                $child->setOption('has_parent', true);
                $values['content'] .= $this->view->render($child);
            }
        }

        /** @var Template $template */
        $template = $this->resolver->resolve($nameOrModel, $this);
        return $template->render((array) $values);
    }
}
