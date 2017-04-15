<?php
namespace Devi\Model;

use DateTime;
use Devi\Hydrator\DateTimeStrategy;
use Devi\Hydrator\Hydrator;
use Devi\Hydrator\IntegerStrategy;
use MeekroDB;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $hydrator;
  
  // Constructor
  public function __construct(MeekroDB $database, $table)
  {
    $this->database = $database;
    $this->table = $table;
    $this->hydrator = (new Hydrator)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy);
  }
  
  // Gets a user from the repository
  public function get(int $id): User
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->hydrator->deserialize($result,new User);
  }
  
  // Gets a user by name
  public function getByName(string $name): User
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->hydrator->deserialize($result,new User);
  }
  
  // Gets a user by public key
  public function getByPublicKey(string $public_key): User
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE public_key = %s",$public_key);
    return $this->hydrator->deserialize($result,new User);
  }
  
  // Puts a user into the repository
  public function put(User $user): void
  {
    $array = $this->hydrator->serialize($user);
    $this->database->insert($this->table,$array);
  }
  
  // Patches a user in the repository
  public function patch(User $user): void
  {
    $array = $this->hydrator->serialize($user->setDateModified(new DateTime));
    $this->database->update($this->table,$array,'id = %d',$user->getId());
  }
  
  // Deletes a user from the repository
  public function delete(User $user): void
  {
    $this->database->delete($this->table,'id = %d',$user->getId());
  }
  
  // Gets all users
  public function getAll(): array
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_created ASC");
    return array_map(function($result) {
      return $this->hydrator->deserialize($result,new User);
    },$results);
  }
}
