<?php
namespace Gallerie\Model;

use DateTime;
use JsonSerializable;

class User implements JsonSerializable
{
  // Variables
  private $id;
  private $name;
  private $email;
  private $password;
  private $public_key;
  private $private_key;
  private $date_created;
  private $date_modified;
  
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
  public function getEmail()
  {
    return $this->email;
  }
  public function withEmail($email)
  {
    $this->email = $email;
    return $this;
  }
  public function getPassword()
  {
    return $this->password;
  }
  public function withPassword($password)
  {
    $this->password = $password;
    return $this;
  }
  public function getPublicKey()
  {
    return $this->public_key;
  }
  public function withPublicKey($public_key)
  {
    $this->public_key = $public_key;
    return $this;
  }
  public function getPrivateKey()
  {
    return $this->private_key;
  }
  public function withPrivateKey($private_key)
  {
    $this->private_key = $private_key;
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
  
  // Serialize to JSON
  public function jsonSerialize()
  {
    return [
      'name' => $this->getName(),
      'email' => $this->getEmail(),
      'date_created' => $this->getDateCreated()->format(DateTime::ISO8601),
      'date_modified' => $this->getDateModified()->format(DateTime::ISO8601)
    ];
  }

  // Create a user
  public static function create($name, $email, $password)
  {
    // Return the new user
    return (new User)
      ->withName($name)
      ->withEmail($email)
      ->withPassword($password)
      ->withPublicKey(self::createKey())
      ->withPrivateKey(self::createKey())
      ->withDateCreated(new DateTime)
      ->withDateModified(new DateTime);
  }
  
  // Create a public or private key
  private static function createKey($length = 32)
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
