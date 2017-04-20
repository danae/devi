<?php
namespace Devi\Implementation\PDO;

use DateTime;
use Devi\Model\User;
use Devi\Model\UserRepositoryInterface;
use Devi\Serializer\DateTimeStrategy;
use Devi\Serializer\IntegerStrategy;
use Devi\Serializer\Serializer;
use PDO;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $pdo;
  private $table;
  private $serializer;
  
  // Constructor
  public function __construct(PDO $pdo, $table)
  {
    $this->pdo = $pdo;
    $this->table = $table;
    $this->serializer = (new Serializer)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy);
  }
  
  // Gets a user from the repository
  public function find(int $id): User
  {
    $result = $this->pdo->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->serializer->deserialize($result,new User);
  }
   
  // Gets all users
  public function findAll(): array
  {
    $results = $this->pdo->query("SELECT * from {$this->table} ORDER BY date_created ASC");
    return array_map(function($result) {
      return $this->serializer->deserialize($result,new User);
    },$results);
  }
  
  // Gets a user by name
  public function findByName(string $name): User
  {
    $result = $this->pdo->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->serializer->deserialize($result,new User);
  }
  
  // Gets a user by public key
  public function findByPublicKey(string $public_key): User
  {
    $result = $this->pdo->queryFirstRow("SELECT * FROM {$this->table} WHERE public_key = %s",$public_key);
    return $this->serializer->deserialize($result,new User);
  }
  
  // Puts a user into the repository
  public function create(User $user): void
  {
    $array = $this->serializer->serialize($user);
    $this->pdo->insert($this->table,$array);
  }
  
  // Patches a user in the repository
  public function update(User $user): void
  {
    $array = $this->serializer->serialize($user->setModified(new DateTime));
    $this->pdo->update($this->table,$array,'id = %d',$user->getId());
  }
  
  // Deletes a user from the repository
  public function delete(User $user): void
  {
    $this->pdo->delete($this->table,'id = %d',$user->getId());
  }
}
