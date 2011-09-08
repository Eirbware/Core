<?php

namespace Eirbware\Twig;

use Gregwar\Image\Image;

/**
 * Extension pour supporter les images
 */
class ImageExtension extends \Twig_Extension
{
    public function getFunctions()
    {
	return array(
	    'image' => new \Twig_Function_Method($this, 'image', array('is_safe' => array('html')))
	);
    }

    public function image($path)
    {
	return new Image($path);
    }

    public function getName()
    {
	return 'image';
    }
}
