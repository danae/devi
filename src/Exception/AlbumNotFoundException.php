<?php
namespace Devi\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlbumNotFoundException extends NotFoundHttpException
{
  // Variables
  private $album;
  
  // Constructor
  public function __construct($album)
  {
    parent::__construct('The specified image was not found');
    
    $this->album = $album;
  }
  
  // Return the album
  public function getImage()
  {
    return $this->album;
  }
}
