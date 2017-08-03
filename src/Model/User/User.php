<?php
namespace Devi\Model\User;

use DateTime;
use Devi\Model\ModifiableTrait;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class User implements NormalizableInterface
{
  use ModifiableTrait;
  
  // Variables
  private $id;
  private $name;
  private $email;
  private $password;
  private $publicKey;
  private $privateKey;
  
  // Management
  public function getId()
  {
    return $this->id;
  }
  public function setId($id): self
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
  public function getEmail(): string
  {
    return $this->email;
  }
  public function setEmail(string $email): self
  {
    $this->email = $email;
    return $this;
  }
  public function getPassword(): string
  {
    return $this->password;
  }
  public function setPassword(string $password): self
  {
    $this->password = $password;
    return $this;
  }
  public function getPublicKey(): string
  {
    return $this->publicKey;
  }
  public function setPublicKey(string $publicKey): self
  {
    $this->publicKey = $publicKey;
    return $this;
  }
  public function getPrivateKey(): string
  {
    return $this->privateKey;
  }
  public function setPrivateKey(string $privateKey): self
  {
    $this->privateKey = $privateKey;
    return $this;
  }
  
  // Normalize the user for use in a database
  public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array
  {
    return [
      'name' => $this->getName(),
      'email' => $this->getEmail(),
      'createdAt' => $normalizer->normalize($this->getCreatedAt(),$format,$context),
      'modifiedAt' => $normalizer->normalize($this->getModifiedAt(),$format,$context)
    ];
  }

  // Create a user
  public static function create($name, $email, $password): self
  {
    global $app;
    
    // Get already occupied names
    $occupied = $app['users.repository']->findAllIds();
    if (in_array($name,$occupied))
      throw new PreconditionFailedHttpException('User name already in use');
    
    // Return the new user
    return (new User)
      ->setName($name)
      ->setEmail($email)
      ->setPassword($password)
      ->setPublicKey(self::createKey())
      ->setPrivateKey(self::createKey())
      ->setCreatedAt(new DateTime)
      ->setModifiedAt(new DateTime);
  }
  
  // Create a public or private key
  private static function createKey($length = 32): string
  {    
    // Create pattern
    $pattern = '0123456789abcdefghijklmnopqrstuvwxyz';
    
    // Generate a key
    for ($i = 0; $i < $length; $i ++)
      $generated .= $pattern[mt_rand(0,strlen($pattern)-1)];
    
    // Return the generated key
    return $generated;
  }
}
