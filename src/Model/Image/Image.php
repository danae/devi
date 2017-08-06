<?php
namespace Devi\Model\Image;

use DateTime;
use Devi\Model\ModifiableTrait;
use Devi\Model\OwnableTrait;
use Devi\Model\User\User;
use Devi\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Image implements NormalizableInterface
{
  use ModifiableTrait;
  use OwnableTrait;
  
  // Variables
  private $id;
  private $name;
  private $contentType;
  private $contentLength;
  private $public;
  
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
  public function getName(): string
  {
    return $this->name;
  }
  public function setName(string $name): self
  {
    $this->name = $name;
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
  
  // Normalize the image for a response
  public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array
  {
    global $app;
    
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'contentType' => $this->getContentType(),
      'contentLength' => (int)$this->getContentLength(),
      'createdAt' => $normalizer->normalize($this->getCreatedAt(),$format,$context),
      'modifiedAt' => $normalizer->normalize($this->getModifiedAt(),$format,$context),
      'public' => (bool)$this->isPublic(),
      'user' => $normalizer->normalize($app['users.repository']->find($this->getUserId()),$format,$context),
        
      // URLs for image blobs
      'imageUrl' => $app['url_generator']->generate('blob.image',[
        'image' => $this->getId(),
        'format' => array_search($this->getContentType(),$app['mimetypes'])
      ],UrlGenerator::ABSOLUTE_URL),
      'thumbnailUrl' => $app['url_generator']->generate('blob.thumbnail',[
        'image' => $this->getId(),
        'width' => 150,
        'height' => 150
      ],UrlGenerator::ABSOLUTE_URL)
    ];
  }
  
  // Post the raw file from an uploaded file
  public function upload(StorageInterface $storage, UploadedFile $file, $name = null): self
  {
    // Upload the file
    $stream = fopen($file->getPathname(),'rb');
    $storage->writeStream($this->getId(),$stream);
    fclose($stream);
    
    // Return the updated file
    return $this
      ->setName($name ?? ($file->getClientOriginalName() ?? $this->getName()))
      ->setContentType($file->getMimeType())
      ->setContentLength($file->getSize());
  }
  
  // Get the raw file as a BinaryFileResponse
  public function respond(StorageInterface $storage): Response
  {
    // Get the response from the storage
    $response = $storage->respond($this->getId(),$this->getName(),$this->getContentType());
    $response->setLastModified($this->getModifiedAt());
    
    // Return the response
    return $response;
  }
  
  // Create an image
  public static function create(User $user): self
  {
    // Return the new file
    return (new self)
      ->setId(self::createId())
      ->setUserId($user->getId())
      ->setCreatedAt(new DateTime)
      ->setModifiedAt(new DateTime)
      ->setPublic(true);
  }
  
  // Generate an image identifier
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