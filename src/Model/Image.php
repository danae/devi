<?php
namespace Devi\Model;

use DateTime;
use JsonSerializable;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

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
  public function getId(): int
  {
    return $this->id;
  }
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }
  public function getName(): string
  {
    return $this->name;
  }
  public function setName(string $name): self
  {
    $this->name = $name;
    return $this;
  }
  public function getFileName(): string
  {
    return $this->file_name;
  }
  public function setFileName(string $file_name): self
  {
    $this->file_name = $file_name;
    return $this;
  }
  public function getFileMimeType(): string
  {
    return $this->file_mime_type;
  }
  public function setFileMimeType(string $file_mime_type): self
  {
    $this->file_mime_type = $file_mime_type;
    return $this;
  }
  public function getFileSize(): int
  {
    return $this->file_size;
  }
  public function setFileSize(int $file_size): self
  {
    $this->file_size = $file_size;
    return $this;
  }
  public function getCreated(): DateTime
  {
    return $this->date_created;
  }
  public function setCreated(DateTime $created): self
  {
    $this->date_created = $created;
    return $this;
  }
  public function getModified(): DateTime
  {
    return $this->date_modified;
  }
  public function setModified(DateTime $modified): self
  {
    $this->date_modified = $modified;
    return $this;
  }
  public function isPublic(): bool
  {
    return $this->public;
  }
  public function setPublic(bool $public)
  {
    $this->public = $public;
    return $this;
  }
  public function getUserId(): int
  {
    return $this->user_id;
  }
  public function setUserId(int $user_id): self
  {
    $this->user_id = $user_id;
    return $this;
  }
  
  // Post the raw image from an uploaded file
  public function upload(StorageInterface $storage, UploadedFile $file, $file_name = null): self
  {
    // Upload the file
    $storage->writeStream($this->name,$file->openFile('rb'));
    
    // Return the updated image
    return $this
      ->setFileName($file_name ?? ($this->getFileName() ?? $file->getClientOriginalName()))
      ->setFileMimeType($file->getMimeType())
      ->setFileSize($file->getSize());
  }
  
  // Get the raw image as a BinaryFileResponse
  public function response(Filesystem $filesystem): Response
  {
    // Get the file location
    $response = $filesystem->respondGzipped('image-' . $this->getName() . '.gz',$this->getFileMimeType(),$this->getFileName());
    
    // Set the correct content headers
    $response->setLastModified($this->getModified());
    
    // Return the response
    return $response;
  }
  
  // Serialize to JSON
  public function jsonSerialize(): array
  {
    global $app;
    
    return [
      'name' => $this->getName(),
      'file_name' => $this->getFileName(),
      'file_mime_type' => $this->getFileMimeType(),
      'file_size' => $this->getFileSize(),
      'created' => $this->getCreated()->format(DateTime::ISO8601),
      'modified' => $this->getModified()->format(DateTime::ISO8601),
      'public' => $this->isPublic(),
      'user' => $app['users.repository']->find($this->getUserId())
    ];
  }
  
  // Create an image
  public static function create(User $user): self
  {
    // Return the new image
    return (new Image)
      ->setUserId($user->getId())
      ->setName(self::createName())
      ->setCreated(new DateTime)
      ->setModified(new DateTime)
      ->setPublic(true);
  }
  
  // Generate an image name
  private static function createName($length = null): string
  {
    global $app;
    
    // Create pattern
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    // Get already occupied names
    $occupied = $app['images.repository']->findAllNames();
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