<?php
namespace Devi\Model\Image;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageNotFoundException extends NotFoundHttpException
{
  // Variables
  private $image;
  
  // Constructor
  public function __construct($image)
  {
    parent::__construct('The specified image was not found');
    $this->image = $image;
  }
  
  // Return the image
  public function getImage()
  {
    return $this->image;
  }
}
