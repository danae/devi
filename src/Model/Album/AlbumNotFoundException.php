<?php
namespace Devi\Model\Album;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlbumNotFoundException extends NotFoundHttpException
{
  // Variables
  private $album;
  
  // Constructor
  public function __construct($album)
  {
    parent::__construct('The specified album was not found');
    $this->album = $album;
  }
  
  // Return the album
  public function getAlbum()
  {
    return $this->album;
  }
}
