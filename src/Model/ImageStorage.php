<?php
namespace Devi\Model;

use Symfony\Component\HttpFoundation\File\File;

class ImageStorage implements ImageStorageInterface
{
  // Variables
  private $location;
  
  // Constructor
  public function __construct($location)
  {
    $this->location = $location;
  }
  
  // Management
  public function getLocation()
  {
    return $this->location;
  }
  public function withLocation($location)
  {
    $this->location = $location;
    return $this;
  }
  
  // Returns the file location for an id
  public function locationFor($id)
  {
    return sprintf($this->getLocation(),$id);
  }
  
  // Gets a file from the storage
  public function get($id)
  {
    return new File($this->locationFor($id),false);
  }  
  
  // Puts a file into the storage
  public function put($id, File $file)
  {
    // Create a new file to store the uploaded file in
    $uploaded_file = new File($this->locationFor($id),false);
    
    // Open and copy buffers
    $file_buffer = $file->openFile('rb');
    $compressed_file = new File('compress.zlib://' . $uploaded_file->getPathname(),false);
    $compressed_buffer = $compressed_file->openFile('wb9');
    while ($file_buffer->valid())
      $compressed_buffer->fwrite($file_buffer->fread(1));
    
    // Return the created file
    return $uploaded_file;
  }
  
  // Deletes a file from the storage
  public function delete($id)
  {
    $location = $this->locationFor($id);
    $real_location = realpath($location);
    if (is_writable($real_location))
      unlink($real_location);
  }
}