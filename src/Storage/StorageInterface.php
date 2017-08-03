<?php
namespace Devi\Storage;

use Symfony\Component\HttpFoundation\Response;

interface StorageInterface 
{
  // Read the contents of a file
  public function read($index);
  
  // Read the contents of a file as a stream
  public function readStream($index);
  
  // Write a string to a file
  public function write($index, $contents);
  
  // Write a stream to a file
  public function writeStream($index, $stream);
  
  // Delete a file
  public function delete($index);
  
  // Create a response with the contents of a file
  public function respond($index, $filename, $filetype): Response;
}
