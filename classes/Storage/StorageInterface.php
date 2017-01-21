<?php
namespace Gallerie\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageInterface 
{
  // Get a file from the storage
  public function get($id);
  
  // Upload a file to the storage
  public function upload($id, UploadedFile $file);
  
  // Delete a file from the storage
  public function delete($id);
}
