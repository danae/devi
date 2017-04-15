<?php
namespace Devi\Model;

use Symfony\Component\HttpFoundation\File\File;

interface ImageStorageInterface 
{
  // Gets a file from the storage
  public function get($id);
  
  // Puts a file into the storage
  public function put($id, File $file);
  
  // Deletes a file from the storage
  public function delete($id);
}
