<?php

// Build-time rasterization of the supplied vector asset; no geometry changes.
$image = new Imagick;
$image->setBackgroundColor('transparent');
$image->setResolution(144, 144);
$image->readImage(__DIR__.'/../public/brand/kanvi-mark.svg');
$image->setImageFormat('png');
$image->writeImage(__DIR__.'/../resources/images/kanvi-mark.png');
