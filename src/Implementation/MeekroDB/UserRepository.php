<?php
namespace Devi\Implementation\MeekroDB;

use DateTime;
use Devi\Model\User;
use Devi\Model\UserRepositoryInterface;
use Devi\Serializer\DateTimeStrategy;
use Devi\Serializer\IntegerStrategy;
use Devi\Serializer\Serializer;
use MeekroDB;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $serializer;
  
  // Constructor
  public function __construct(MeekroDB $database, $table)
  {
    $this->database = $database;
    $this->table = $table;
    $this->serializer = (new Serializer)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy);
  }
  
  // Puts a user into the repository
  public function create(User $user): void
  {
    $array = $this->serializer->serialize($user);
    $this->database->insert($this->table,$array);
  }
  
  // Gets a user from the repository
  public function retrieve(int $id): User
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->serializer->deserialize($result,new User);
  }
   
  // Gets all users
  public function retreiveAll(): array
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_created ASC");
    return array_map(function($result) {
      return $this->serializer->deserialize($result,new User);
    },$results);
  }
  
  // Gets a user by name
  public function retrieveByName(string $name): User
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->serializer->deserialize($result,new User);
  }
  
  // Gets a user by public key
  public function retrieveByPublicKey(string $public_key): User
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE public_key = %s",$public_key);
    return $this->serializer->deserialize($result,new User);
  }
  
  // Patches a user in the repository
  public function update(User $user): void
  {
    $array = $this->serializer->serialize($user->setDateModified(new DateTime));
    $this->database->update($this->table,$array,'id = %d',$user->getId());
  }
  
  // Deletes a user from the repository
  public function delete(User $user): void
  {
    $this->database->delete($this->table,'id = %d',$user->getId());
  }
}
