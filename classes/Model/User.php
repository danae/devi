<?php
namespace Gallerie\Model;

class User
{
  // Variables
  private $id;
  private $name;
  private $email;
  private $password;
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

  // Create a user
  public static function create($name, $email, $password)
  {
    // Return the new art
    return (new User)
      ->withName($name)
      ->withEmail($email)
      ->withPassword($password)
      ->withDateCreated(new DateTime)
      ->withDateModified(new DateTime);
  }
}
