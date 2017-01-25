<?php
namespace Gallerie\Model;

use DateTime;
use JsonSerializable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Image implements JsonSerializable
{
  // Variables
  private $id;
  private $name;
  private $file_name;
  private $file_mime_type;
  private $file_size;
  private $date_created;
  private $date_modified;
  private $public;
  private $user_id;
  
  // Management
  public function getId()
  {
    return $this->id;
  }
  public function withId($id)
  {
    $this->id = $id;
    return $this;
  }
  public function getName()
  {
    return $this->name;
  }
  public function withName($name)
  {
    $this->name = $name;
    return $this;
  }
  public function getFileName()
  {
    return $this->file_name;
  }
  public function withFileName($file_name)
  {
    $this->file_name = $file_name;
    return $this;
  }
  public function getFileMimeType()
  {
    return $this->file_mime_type;
  }
  public function withFileMimeType($file_mime_type)
  {
    $this->file_mime_type = $file_mime_type;
    return $this;
  }
  public function getFileSize()
  {
    return $this->file_size;
  }
  public function withFileSize($file_size)
  {
    $this->file_size = $file_size;
    return $this;
  }
  public function getDateCreated()
  {
    return $this->date_created;
  }
  public function withDateCreated($date_created)
  {
    $this->date_created = $date_created;
    return $this;
  }
  public function getDateModified()
  {
    return $this->date_modified;
  }
  public function withDateModified($date_modified)
  {
    $this->date_modified = $date_modified;
    return $this;
  }
  public function isPublic()
  {
    return $this->public;
  }
  public function withPublic($public)
  {
    $this->public = $public;
    return $this;
  }
  public function getUserId()
  {
    return $this->user_id;
  }
  public function withUserId($user_id)
  {
    $this->user_id = $user_id;
    return $this;
  }
  
  // Post the raw image from an uploaded file
  public function upload(ImageStorageInterface $storage, UploadedFile $file, $file_name = null)
  {
    // Upload the file
    $storage->put($this->name,$file);
    
    // Return the updated image
    return $this
      ->withFileName($file_name !== null ? $file_name : ($this->getFileName() !== null ? $this->getFileName() : $file->getClientOriginalName()))
      ->withFileMimeType($file->getMimeType())
      ->withFileSize($file->getSize());
  }
  
  // Get the raw image as a BinaryFileResponse
  public function response(ImageStorageInterface $storage)
  {
    // Get the file location
    $file = $storage->get($this->getName());
  
    // Create the response
    $response = new BinaryFileResponse($file);
    $response->setLastModified($this->getDateModified());
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$this->getFileName());
    
    // Set the correct content type
    $response->headers->set('Content-Type',$this->getFileMimeType());
    
    // Set content encoding if this file is gzipped
    if ($file->getMimeType() === 'application/x-gzip')
      $response->headers->set('Content-Encoding','gzip');
    
    // Return the response
    return $response;
  }
  
  // Serialize to JSON
  public function jsonSerialize()
  {
    global $app;
    
    return [
      'name' => $this->getName(),
      'file_name' => $this->getFileName(),
      'file_mime_type' => $this->getFileMimeType(),
      'file_size' => $this->getFileSize(),
      'date_created' => $this->getDateCreated()->format(DateTime::ISO8601),
      'date_modified' => $this->getDateModified()->format(DateTime::ISO8601),
      'public' => $this->isPublic(),
      'user_name' => $app['users.repository']->get($this->getUserId())->getName()
    ];
  }
  
  // Create an image
  public static function create(User $user)
  {
    // Return the new image
    return (new Image)
      ->withUserId($user->getId())
      ->withName(self::createName())
      ->withDateCreated(new DateTime)
      ->withDateModified(new DateTime)
      ->withPublic(true);
  }
  
  // Generate an image name
  private static function createName($length = null)
  {
    global $app;
    
    // Create pattern
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    // Get already occupied names
    $occupied = $app['images.repository']->getAllNames();
    $occupied_order = ceil(log(count($occupied),36)) + 1;
    
    // Set length
    if ($length == null)
      $length = max([$occupied_order + 1,5]);
    
    // Generate a name
    do {
      $generated = '0';
      for ($i = 1; $i < $length; $i ++)
        $generated .= $pattern[mt_rand(0,strlen($pattern)-1)];
    } while (in_array($generated,$occupied));
    
    // Return the generated name
    return $generated;
  }
}