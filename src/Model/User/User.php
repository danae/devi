<?php
namespace Devi\Model\User;

use DateTime;
use Devi\App\ApplicationException;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class User implements NormalizableInterface, DenormalizableInterface
{
  // Variables
  private $id;
  private $name;
  private $email;
  private $password;
  private $public_key;
  private $private_key;
  private $created_at;
  private $modified_at;
  
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
    return $this->public_key;
  }
  public function setPublicKey(string $public_key): self
  {
    $this->public_key = $public_key;
    return $this;
  }
  public function getPrivateKey(): string
  {
    return $this->private_key;
  }
  public function setPrivateKey(string $private_key): self
  {
    $this->private_key = $private_key;
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
  
  // Normalize the user for use in a database
  public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array
  {
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'email' => $this->getEmail(),
      'password' => $this->getPassword(),
      'public_key' => $this->getPublicKey(),
      'private_key' => $this->getPrivateKey(),
      'created_at' => $normalizer->normalize($this->getCreatedAt(),$format,$context),
      'modified_at' => $normalizer->normalize($this->getModifiedAt(),$format,$context)
    ];
  }
  
  // Denormalize the user
  public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): User
  {
    return $this
      ->setId($data['id'])
      ->setName($data['name'])
      ->setEmail($data['email'])
      ->setPassword($data['password'])
      ->setPublicKey($data['public_key'])
      ->setPrivateKey($data['private_key'])
      ->setCreatedAt($denormalizer->denormalize($data['created_at'],DateTime::class,$format,$context))
      ->setModifiedAt($denormalizer->denormalize($data['modified_at'],DateTime::class,$format,$context));
  }

  // Create a user
  public static function create($name, $email, $password): self
  {
    global $app;
    
    // Get already occupied names
    $occupied = $app['users.repository']->findAllIds();
    if (in_array($name,$occupied))
      throw new ApplicationException('User name already in use');
    
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
