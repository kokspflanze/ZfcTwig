<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Twig\Extension\AbstractExtension;
use ZfcTwig\View\TwigRenderer;

class Extension extends AbstractExtension
{
    /** @var TwigRenderer */
    protected $renderer;

    public function __construct(TwigRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getRenderer(): TwigRenderer
    {
        return $this->renderer;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return self::class;
    }
}
