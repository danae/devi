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
  
  // Constructor
  public function __construct(Filesystem $filesystem)
  {
    $this->filesystem = $filesystem;
  }
  
  // Returns if a file exists
  public function has($index)
  {
    // Check if the file is present
    return $this->filesystem->has($index);
  }
  
  // Read the contents of a file
  public function read($index)
  {
    // Read the file from the filesystem
    return $this->filesystem->read($index);
  }
  
  // Read the contents of a file as a stream
  public function readStream($index)
  {
    // Read the file from the filesystem
    return $this->filesystem->readStream($index);
  }

  // Write a string to a file
  public function write($index, $contents)
  {
    // Put the file in the filesystem
    $this->filesystem->put($index,$contents);
  }
  
  // Write a stream to a file
  public function writeStream($index, $stream)
  {
    // Validate the stream
    if (!is_resource($stream))
      throw new Exception("The stream is not a resource");
    
    // Put the file in the filesystem
    $this->filesystem->putStream($index,$stream);
  }
  
  // Delete a file
  public function delete($index)
  {
    // Delete the file from the filesystem
    $this->filesystem->delete($index);
  }
  
  // Get the mime type of a file
  public function getMimeType($index)
  {
    // Return the MIME type
    return $this->filesystem->getMimetype($index);
  }
  
  // Create a response with the contents of a file
  public function respond($index, $filename): Response
  {
    // Get the type and contents of the file
    $filetype = $this->getMimeType($index);
    $contents = $this->read($index);
    
    // Create a new response
    $response = new Response($contents);
    
    // Set the response headers
    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$index);
    $response->headers->set('Content-Disposition',$disposition);
    $response->headers->set('Content-Type',$filetype);
    
    // Return the response
    return $response;
  }
}
