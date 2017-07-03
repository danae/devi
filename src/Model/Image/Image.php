<?php
namespace Devi\Model\Image;

use DateTime;
use Devi\Model\Storage\StorageInterface;
use Devi\Model\User\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Image implements NormalizableInterface, DenormalizableInterface
{
  // Variables
  private $id;
  private $file_name;
  private $content_type;
  private $content_length;
  private $created_at;
  private $modified_at;
  private $public;
  private $user_id;
  
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
    return $this->file_name;
  }
  public function setFileName(string $file_name): self
  {
    $this->file_name = $file_name;
    return $this;
  }
  public function getContentType(): string
  {
    return $this->content_type;
  }
  public function setContentType(string $content_type): self
  {
    $this->content_type = $content_type;
    return $this;
  }
  public function getContentLength(): int
  {
    return $this->content_length;
  }
  public function setContentLength(int $content_length): self
  {
    $this->content_length = $content_length;
    return $this;
  }
  public function getCreatedAt(): DateTime
  {
    return $this->created_at;
  }
  public function setCreatedAt(DateTime $created_at): self
  {
    $this->created_at = $created_at;
    return $this;
  }
  public function getModifiedAt(): DateTime
  {
    return $this->modified_at;
  }
  public function setModifiedAt(DateTime $modified_at): self
  {
    $this->modified_at = $modified_at;
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
  
  // Normalize the image for use in a database
  public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array
  {
    return [
      'id' => $this->getId(),
      'file_name' => $this->getFileName(),
      'content_type' => $this->getContentType(),
      'content_length' => (int)$this->getContentLength(),
      'created_at' => $normalizer->normalize($this->getCreatedAt(),$format,$context),
      'modified_at' => $normalizer->normalize($this->getModifiedAt(),$format,$context),
      'public' => (bool)$this->isPublic(),
      'user_id' => (int)$this->getUserId()
    ];
  }
  
  // Denormalize the image
  public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): Image
  {
    return $this
      ->setId($data['id'])
      ->setFileName($data['file_name'])
      ->setContentType($data['content_type'])
      ->setContentLength((int)$data['content_length'])
      ->setCreatedAt($denormalizer->denormalize($data['created_at'],DateTime::class,$format,$context))
      ->setModifiedAt($denormalizer->denormalize($data['modified_at'],DateTime::class,$format,$context))
      ->setPublic((bool)$data['public'])
      ->setUserId($data['user_id']);
  }
  
  // Post the raw image from an uploaded file
  public function upload(StorageInterface $storage, UploadedFile $file, $file_name = null): self
  {
    // Upload the file
    $stream = fopen($file->getPathname(),'rb');
    $storage->writeStream($this->getId(),$stream);
    fclose($stream);
    
    // Return the updated image
    return $this
      ->setFileName($file_name ?? ($file->getClientOriginalName() ?? $this->getFileName()))
      ->setContentType($file->getMimeType())
      ->setContentLength($file->getSize());
  }
  
  // Get the raw image as a BinaryFileResponse
  public function respond(StorageInterface $storage): Response
  {
    // Get the response from the storage
    $response = $storage->respond($this->getId(),$this->getFileName(),$this->getContentType());
    $response->setLastModified($this->getModifiedAt());
    
    // Return the response
    return $response;
  }
  
  // Create an image
  public static function create(User $user): self
  {
    // Return the new image
    return (new Image)
      ->setId(self::createId())
      ->setUserId($user->getId())
      ->setCreatedAt(new DateTime)
      ->setModifiedAt(new DateTime)
      ->setPublic(true);
  }
  
  // Generate an image id
  private static function createId($length = null): string
  {
    global $app;
    
    // Create pattern
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    // Get already occupied ids
    $occupied = $app['images.repository']->findAllIds();
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