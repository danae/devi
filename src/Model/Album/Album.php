<?php
namespace Devi\Model\Album;

use DateTime;
use Devi\Model\ModifiableTrait;
use Devi\Model\OwnableTrait;
use Devi\Model\User\User;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Album implements NormalizableInterface
{
  use ModifiableTrait;
  use OwnableTrait;
  
  // Variables
  private $id;
  private $name;
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
  public function isPublic(): bool
  {
    return $this->public;
  }
  public function setPublic(bool $public)
  {
    $this->public = $public;
    return $this;
  }
  
  // Normalize the album for a response
  public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array
  {
    global $app;
    
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'createdAt' => $normalizer->normalize($this->getCreatedAt(),$format,$context),
      'modifiedAt' => $normalizer->normalize($this->getModifiedAt(),$format,$context),
      'public' => (bool)$this->isPublic(),
      'user' => $normalizer->normalize($app['users.repository']->find($this->getUserId()),$format,$context)
    ];
  }
  
  // Create an album
  public static function create(User $user): self
  {
    // Return the new file
    return (new self)
      ->setId(self::createId())
      ->setName($user->getName() . '\'s album')
      ->setUserId($user->getId())
      ->setCreatedAt(new DateTime)
      ->setModifiedAt(new DateTime)
      ->setPublic(true);
  }
  
  // Generate an album identifier
  private static function createId($length = null): string
  {
    global $app;
    
    // Create pattern
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    // Get already occupied ids
    $occupied = $app['albums.repository']->findAllIds();
    $occupied_order = ceil(log(count($occupied),36)) + 1;
    
    // Set length
    if ($length == null)
      $length = max([$occupied_order + 1,5]);
    
    // Generate an id
    do {
      $generated = '1';
      for ($i = 1; $i < $length; $i ++)
        $generated .= $pattern[mt_rand(0,strlen($pattern)-1)];
    } while (in_array($generated,$occupied));
    
    // Return the generated id
    return $generated;
  }
}
