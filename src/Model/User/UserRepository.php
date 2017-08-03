<?php
namespace Devi\Model\User;

use Devi\Utils\Database;
use PDO;
use Symfony\Component\Serializer\Serializer;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $serializer;
  
  // Constructor
  public function __construct(Database $database, string $table, Serializer $serializer)
  {
    $this->database = $database;
    $this->table = $table;
    $this->serializer = $serializer;
  }
  
  // Get a user from the repository
  public function find(string $id): User
  {
    $data = $this->database->selectOne($this->table,['id' => $id]);
    if ($data === null)
      throw new UserNotFoundException($id);
    else
      return $this->serializer->denormalize($data,User::class);
  }
  
  // Get a user by name
  public function findByName(string $name): User
  {
    $data = $this->database->selectOne($this->table,['name' => $name]);
    if ($data === null)
      throw new UserNotFoundException($name);
    else
      return $this->serializer->denormalize($data,User::class);
  }
  
  // Get a user by public key
  public function findByPublicKey(string $publicKey): User
  {
    $data = $this->database->selectOne($this->table,['publicKey' => $publicKey]);
    if ($data === null)
      throw new UserNotFoundException($publicKey);
    else
      return $this->serializer->denormalize($data,User::class);
  }
  
  // Get all users
  public function findAll(): array
  {
    $data = $this->database->select($this->table,[],'modifiedAt desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,User::class);
    },$data);
  }
  
  // Get all ids as an array
  public function findAllIds(): array
  {
    $st = $this->database->query("SELECT name FROM {$this->table}");
    return $st->fetchAll(PDO::FETCH_NUM);
  }
  
  // Create a user in the repository
  public function create(User $user)
  {
    $array = $this->serializer->normalize($user);
    $this->database->insert($this->table,$array);
  }
  
  // Update a user in the repository
  public function update(User $user)
  {
    $array = $this->serializer->normalize($user);
    $this->database->update($this->table,$array,['id' => $user->getId()]);
  }
  
  // Delete a user from the repository
  public function delete(User $user)
  {
    $this->database->delete($this->table,['id' => $user->getId()]);
  }
}
