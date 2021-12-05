<?php

declare(strict_types=1);

namespace ZfcTwig\View;

use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Resolver\ResolverInterface;
use Twig\Environment;

class TwigResolver implements ResolverInterface
{
    /** @var Environment */
    protected $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function resolve($name, ?Renderer $renderer = null)
    {
        return $this->environment->load($name);
    }
}
