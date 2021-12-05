<?php

namespace View;

use Laminas\View\Model\ModelInterface;
use Laminas\View\View;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader;
use ZfcTwig\View\TwigRenderer;
use ZfcTwig\View\TwigResolver;

class TwigRendererTest extends TestCase
{
    /** @var  TwigRenderer */
    protected $renderer;

    public function setUp(): void
    {
        parent::setUp();

        $chain = new Loader\ChainLoader();
        $chain->addLoader(new Loader\ArrayLoader(['key1' => 'var1 {{ foobar }}']));
        $environment = new Environment($chain);
        $this->renderer = new TwigRenderer(new View, $chain, $environment, new TwigResolver($environment));
    }

    public function testRenderWithName()
    {
        $content = $this->renderer->render('key1');

        $this->assertIsString($content);
        $this->assertSame('var1 ', $content);
    }

    public function testRenderWithNameAndValues()
    {
        $content = $this->renderer->render('key1', ['foobar' => 'baz']);

        $this->assertIsString($content);
        $this->assertSame('var1 baz', $content);
    }

    public function testRenderWithModelAndValues()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ModelInterface $model */
        $model = $this->getMockBuilder(ModelInterface::class)->getMock();
        $model->expects($this->exactly(1))
            ->method('getTemplate')
            ->willReturn('key1');
        $model->expects($this->exactly(1))
            ->method('getVariables')
            ->willReturn(['foobar' => 'baz']);

        $content = $this->renderer->render($model);

        $this->assertIsString($content);
        $this->assertSame('var1 baz', $content);
    }

}
