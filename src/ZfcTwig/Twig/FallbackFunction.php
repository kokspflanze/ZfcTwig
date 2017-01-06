<?php

namespace ZfcTwig\Twig;

use \Twig_Function;
use Zend\View\Helper\HelperInterface;

/**
 * Class FallbackFunction
 *
* @package ZfcTwig\Twig
*/
class FallbackFunction extends Twig_Function
{
    /**
     * @var HelperInterface
     */
    protected $helper;

    public function __construct($helper)
    {
        $this->helper = $helper;

        parent::__construct($helper, $this->compile(), ['is_safe' => ['all']]);
    }

    /**
     * Compiles a function.
     *
     * @return string The PHP code for the function
     */
    public function compile()
    {
        return sprintf(
            '$this->env->getExtension("%s")->getRenderer()->plugin("%s")->__invoke', Extension::class, $this->helper
        );
    }
}
