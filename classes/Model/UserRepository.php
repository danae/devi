<?php
namespace Gallerie\Model;

use DateTime;
use MeekroDB;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  
  // Constructor
  public function __construct(MeekroDB $database, $table)
  {
    $this->database = $database;
    $this->table = $table;
  }
  
  // Convert a user from an associative array
  private function fromArray($array)
  {
    if ($array == null)
      return null;
    
    return (new User)
      ->withId((int)$array['id'])
      ->withName($array['name'])
      ->withEmail($array['email'])    
      ->withPassword($array['password'])
      ->withPublicKey($array['public_key'])
      ->withPrivateKey($array['private_key'])
      ->withDateCreated(new DateTime($array['date_created']))
      ->withDateModified(new DateTime($array['date_modified']));
  }
  
  // Convert a user to an associative array
  private function toArray(User $user)
  {
    return [
      'id' => $user->getId(),
      'name' => $user->getName(),
      'email' => $user->getEmail(),
      'password' => $user->getPassword(),
      'public_key' => $user->getPublicKey(),
      'private_key' => $user->getPrivateKey(),
      'date_created' => $user->getDateCreated(),
      'date_modified' => $user->getDateModified()
    ];
  }
  
  // Gets a user from the repository
  public function get($id)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->fromArray($result);
  }
  
  // Gets a user by name
  public function getByName($name)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->fromArray($result);
  }
  
  // Gets a user by public key
  public function getByPublicKey($public_key)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE public_key = %s",$public_key);
    return $this->fromArray($result);
  }
  
  // Puts a user into the repository
  public function put(User $user)
  {
    $this->database->insert($this->table,$this->toArray($user));
  }
  
  // Patches a user in the repository
  public function patch(User $user)
  {
    $this->database->update($this->table,$this->toArray($user->withDateModified(new DateTime)),'id = %d',$user->getId());
  }
  
  // Deletes a user from the repository
  public function delete(User $user)
  {
    $this->database->delete($this->table,'id = %d',$user->getId());
  }
  
  // Gets all users
  public function getAll()
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_created ASC");
    return array_map([$this,'fromArray'],$results);
  }
}
