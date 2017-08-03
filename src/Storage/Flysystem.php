<?php
namespace Devi\Storage;

use League\Flysystem\Exception;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Flysystem implements StorageInterface
{
  // Variables
  private $filesystem;
  private $format;
  
  // Constructor
  public function __construct(Filesystem $filesystem, string $format = '%s')
  {
    $this->filesystem = $filesystem;
    $this->format = $format;
  }
  
  // Read the contents of a file
  public function read($index)
  {
    $path = sprintf($this->format,$index);
    
    // Read the file from the filesystem
    return $this->filesystem->read($path);
  }
  
  // Read the contents of a file as a stream
  public function readStream($index)
  {
    $path = sprintf($this->format,$index);
    
    // Read the file from the filesystem
    return $this->filesystem->readStream($path);
  }

  // Write a string to a file
  public function write($index, $contents)
  {
    $path = sprintf($this->format,$index);
    
    // Put the file in the filesystem
    $this->filesystem->put($path,$contents);
  }
  
  // Write a stream to a file
  public function writeStream($index, $stream)
  {
    $path = sprintf($this->format,$index);
    
    // Validate the stream
    if (!is_resource($stream))
      throw new Exception("The stream is not a resource");
    
    // Put the file in the filesystem
    $this->filesystem->putStream($path,$stream);
  }
  
  // Delete a file
  public function delete($index)
  {
    $path = sprintf($this->format,$index);
    
    // Delete the file from the filesystem
    $this->filesystem->delete($path);
  }
  
  // Create a response with the contents of a file
  public function respond($index, $filename, $filetype): Response
  {
    // Get the contents of the file
    $contents = $this->read($index);
    
    // Create a new response
    $response = new Response($contents);
    
    // Set the response headers
    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
    $response->headers->set('Content-Disposition',$disposition);
    $response->headers->set('Content-Type',$filetype);
    
    // Return the response
    return $response;
  }
}
