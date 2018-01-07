<?php
namespace Devi\Model\Image;

use Devi\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StoredImage extends Image
{
  // Variables
  private $storage;
  
  // Constructor
  public function __construct(Image $image, StorageInterface $storage)
  {
    $this->setId($image->getId());
    $this->setName($image->getName());
    $this->setWidth($image->getWidth());
    $this->setHeight($image->getHeight());
    $this->setContentType($image->getContentType());
    $this->setContentLength($image->getContentLength());
    $this->setCreatedAt($image->getCreatedAt());
    $this->setModifiedAt($image->getModifiedAt());
    $this->setPublic($image->isPublic());
    $this->setUserId($image->getUserId());
    
    $this->storage = $storage;
  }
  
   // Post the raw file from an uploaded file
  public function upload(UploadedFile $file, $name = null): self
  {
    // Upload the file
    $stream = fopen($file->getPathname(),'rb');
    $this->storage->writeStream($this->getId(),$stream);
    fclose($stream);
    
    // Get the image size
    list($width,$height) = getimagesize($file->getPathname());
    
    // Return the updated file
    return $this
      ->setName($name ?? ($file->getClientOriginalName() ?? $this->getName()))
      ->setWidth($width)
      ->setHeight($height)
      ->setContentType($file->getMimeType())
      ->setContentLength($file->getSize());
  }
  
  // Get the raw file as a BinaryFileResponse
  public function respond(): Response
  {
    // Read the image contents
    $contents = $this->storage->read($this->getId());
    
    // Create a new response
    $response = new Response($contents);
    
    // Set the response headers
    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$index);
    $response->headers->set('Content-Disposition',$disposition);
    $response->headers->set('Content-Type',$this->getContentType());
    
    // Return the response
    $response->setLastModified($this->getModifiedAt());
    
    // Return the response
    return $response;
  }
}
