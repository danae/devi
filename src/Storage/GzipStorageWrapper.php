<?php
namespace Devi\Storage;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class GzipStorageWrapper implements StorageInterface
{
  // Constants
  const gz_suffix = ".gz";
  const mimetype_suffix = ".mimetype";
  
  // Variables
  private $storage;
  
  // Constructor
  public function __construct(StorageInterface $storage)
  {
    $this->storage = $storage;
  }
  
  // Returns if a file exists
  public function has($index)
  {
    // Check if the file is present
    return $this->filesystem->assertPresent($index . self::gz_suffix);
  }
  
  // Read the contents of a file
  public function read($index)
  {
    // Read the file from the storage
    $contents = $this->storage->read($index . self::gz_suffix);
    
    // Uncompress the contents
    $contents = gzdecode($contents);
    
    // Return the contents
    return $contents;
  }
  
  // Read the contents of a file as a stream
  public function readStream($index)
  {
    // Read the contetns of the file
    $contents = $this->read($index);
    
    // Create a stream of the contents
    $stream = fopen("php://temp","wb+");
    fwrite($stream,$contents);
    rewind($stream);
    
    // Return the stream
    return $stream;
  }

  // Write a string to a file
  public function write($index, $contents, $level = 9)
  {
    // Compress the contents
    $contents = gzencode($contents,$level);
    
    // Write the contents to the storage
    $this->storage->write($index . self::gz_suffix,$contents);
  }
  
  // Write a stream to a file
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
  
  // Delete a file
  public function delete($index)
  {
    // Pass delete to the storage
    $this->storage->delete($index . self::gz_suffix);
  }
  
  // Get the MIME type of a file
  public function getMimeType($index)
  {
    // Check if there exists a .mimetype file
    if ($this->storage->has($index . self::mimetype_suffix))
    {
      // Read the contents and return them
      $mimetype = $this->storage->read($index . self::mimetype_suffix);
      return $mimetype;
    }
    else
    {
      // Return the actual MIME type (application/x-gzip)
      //return $this->storage->getMimeType($index . self::gz_suffix);
      return '';
    }
  }
  
  // Create a response with the contents of a file
  public function respond($index, $filenamex): Response
  {
    // Get the response of the storage
    $response = $this->storage->respond($index . self::gz_suffix,$index);
    
    // Set the response headers
    $response->headers->set('Content-Encoding','gzip');
    $response->headers->set('Content-Type',$this->getMimeType($index));
    
    // Return the response
    return $response;
  }
}
