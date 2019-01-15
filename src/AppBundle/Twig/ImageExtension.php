<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('resizeImage', [$this, 'resizeImage'])
        ];
    }

    public function resizeImage($src, $width = 150, $height = 150)
    {
        return "src={$src} width={$width} height={$height}";
    }
}
