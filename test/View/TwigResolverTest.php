<?php

declare(strict_types=1);

namespace View;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader;
use Twig\TemplateWrapper;
use ZfcTwig\View\TwigResolver;

class TwigResolverTest extends TestCase
{
    /** @var  TwigResolver */
    protected $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $chain = new Loader\ChainLoader();
        $chain->addLoader(new Loader\ArrayLoader(['key1' => 'var1']));
        $environment    = new Environment($chain);
        $this->resolver = new TwigResolver($environment);
    }

    public function testResolve()
    {
        $this->assertInstanceOf(TemplateWrapper::class, $this->resolver->resolve('key1'));
    }

    public function testResolveError()
    {
        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Template "key2" is not defined.');
        $this->assertInstanceOf(TemplateWrapper::class, $this->resolver->resolve('key2'));
    }
}
