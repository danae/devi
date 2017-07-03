<?php
namespace Devi\Model\Storage;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class GzipWrapper implements StorageInterface
{
  // Variables
  private $storage;
  
  // Constructor
  public function __construct(StorageInterface $storage)
  {
    $this->storage = $storage;
  }
  
  // Read the contents of a blob
  public function read($index)
  {
    // Read the file from the storage
    $contents = $this->storage->read($index);
    
    // Uncompress the contents
    $contents = gzdecode($contents);
    
    // Return the contents
    return $contents;
  }
  
  // Read the contents of a blob as a stream
  public function readStream($index)
  {
    // Read the contetns of the blob
    $contents = $this->read($index);
    
    // Create a stream of the contents
    $stream = fopen("php://temp","wb+");
    fwrite($stream,$contents);
    rewind($stream);
    
    // Return the stream
    return $stream;
  }

  // Write a string to a blob
  public function write($index, $contents, $level = 9)
  {
    // Compress the contents
    $contents = gzencode($contents,$level);
    
    // Write the contents to the storage
    $this->storage->write($index,$contents);
  }
  
  // Write a stream to a blob
  public function writeStream($index, $stream, $level = 9)
  {
    // Validate the stream
    if (!is_resource($stream))
      throw new Exception("The stream is not a resource");
    
    // Read the contents of the stream
    $contents = stream_get_contents($stream);
    
    // Write the contents of the stream
    $this->write($index,$contents,$level);
  }
  
  // Delete a blob
  public function delete($index)
  {
    // Pass delete to the storage
    $this->storage->delete($index);
  }
  
  // Create a response with the contents of a blob
  public function respond($index, $filename, $filetype): Response
  {
    // Get the response of the storage
    $response = $this->storage->respond($index,$filename,$filetype);
    
    // Set the response headers
    $response->headers->set('Content-Encoding','gzip');
    
    // Return the response
    return $response;
  }
}
