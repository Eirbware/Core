<?php

namespace Eirbware\Twig;

use Gregwar\Image\Image;

/**
 * Extension Twig de Eirbware
 */
class Extension extends \Twig_Extension
{
    private $app;

    public function __construct($app)
    {
	$this->app = $app;
    }

    public function getFunctions()
    {
	return array(
            'image' => new \Twig_Function_Method($this, 'image', array('is_safe' => array('html'))),
            'nl2br' => new \Twig_Function_Method($this, 'nl2br', array('is_safe' => array('html'))),
            'path' => new \Twig_Function_Method($this, 'path', array('is_safe' => array('html'))),
	);
    }

    public function image($path)
    {
	return new Image($path);
    }

    public function nl2br($str)
    {
        return nl2br($str);
    }

    public function path($name, array $parameters = array())
    {
	return $this->app['url_generator']->generate($name, $parameters);
    }

    public function getName()
    {
	return 'eirbware_extension';
    }
}
