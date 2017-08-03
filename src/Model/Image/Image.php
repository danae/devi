<?php
namespace Devi\Model\Image;

use DateTime;
use Devi\Model\ModifiableTrait;
use Devi\Model\Storage\StorageInterface;
use Devi\Model\User\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Image implements NormalizableInterface
{
  use ModifiableTrait;
  
  // Variables
  private $id;
  private $fileName;
  private $contentType;
  private $contentLength;
  private $public;
  private $userId;
  
  // Management
  public function getId(): string
  {
    return $this->id;
  }
  public function setId(string $id): self
  {
    $this->id = $id;
    return $this;
  }
  public function getFileName(): string
  {
    return $this->fileName;
  }
  public function setFileName(string $fileName): self
  {
    $this->fileName = $fileName;
    return $this;
  }
  public function getContentType(): string
  {
    return $this->contentType;
  }
  public function setContentType(string $contentType): self
  {
    $this->contentType = $contentType;
    return $this;
  }
  public function getContentLength(): int
  {
    return $this->contentLength;
  }
  public function setContentLength(int $contentLength): self
  {
    $this->contentLength = $contentLength;
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
    return $this->userId;
  }
  public function setUserId(int $userId): self
  {
    $this->userId = $userId;
    return $this;
  }
  
  // Normalize the file for use in a database
  public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array
  {
    global $app;
    
    return [
      'id' => $this->getId(),
      'fileName' => $this->getFileName(),
      'contentType' => $this->getContentType(),
      'contentLength' => (int)$this->getContentLength(),
      'createdAt' => $normalizer->normalize($this->getCreatedAt(),$format,$context),
      'modifiedAt' => $normalizer->normalize($this->getModifiedAt(),$format,$context),
      'public' => (bool)$this->isPublic(),
      'user' => $normalizer->normalize($app['users.repository']->find($this->getUserId()),$format,$context)
    ];
  }
  
  // Post the raw file from an uploaded file
  public function upload(StorageInterface $storage, UploadedFile $file, $file_name = null): self
  {
    // Upload the file
    $stream = fopen($file->getPathname(),'rb');
    $storage->writeStream($this->getId(),$stream);
    fclose($stream);
    
    // Return the updated file
    return $this
      ->setFileName($file_name ?? ($file->getClientOriginalName() ?? $this->getFileName()))
      ->setContentType($file->getMimeType())
      ->setContentLength($file->getSize());
  }
  
  // Get the raw file as a BinaryFileResponse
  public function respond(StorageInterface $storage): Response
  {
    // Get the response from the storage
    $response = $storage->respond($this->getId(),$this->getFileName(),$this->getContentType());
    $response->setLastModified($this->getModifiedAt());
    
    // Return the response
    return $response;
  }
  
  // Create a file
  public static function create(User $user): self
  {
    // Return the new file
    return (new Image)
      ->setId(self::createId())
      ->setUserId($user->getId())
      ->setCreatedAt(new DateTime)
      ->setModifiedAt(new DateTime)
      ->setPublic(true);
  }
  
  // Generate a file id
  private static function createId($length = null): string
  {
    global $app;
    
    // Create pattern
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    // Get already occupied ids
    $occupied = $app['files.repository']->findAllIds();
    $occupied_order = ceil(log(count($occupied),36)) + 1;
    
    // Set length
    if ($length == null)
      $length = max([$occupied_order + 1,5]);
    
    // Generate an id
    do {
      $generated = '0';
      for ($i = 1; $i < $length; $i ++)
        $generated .= $pattern[mt_rand(0,strlen($pattern)-1)];
    } while (in_array($generated,$occupied));
    
    // Return the generated id
    return $generated;
  }
}