<?php

namespace Eirbware\Twig;

use Gregwar\Image\Image;

/**
 * Extension Twig de Eirbware
 */
class Extension extends \Twig_Extension
{
    public function getFunctions()
    {
	return array(
            'image' => new \Twig_Function_Method($this, 'image', array('is_safe' => array('html'))),
            'nl2br' => new \Twig_Function_Method($this, 'nl2br', array('is_safe' => array('html'))),
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

    public function getName()
    {
	return 'image';
    }
}
