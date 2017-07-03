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
  
  // Gets a user from the repository
  public function find(string $name)
  {
    $data = $this->database->selectOne($this->table,['name' => $name]);
    if ($data === null)
      return null;
    else
      return $this->serializer->denormalize($data,User::class);
  }
  
  // Gets a user by name
  public function findByName(string $name)
  {
    $data = $this->database->selectOne($this->table,['name' => $name]);
    if ($data === null)
      return null;
    else
      return $this->serializer->denormalize($data,User::class);
  }
  
  // Gets a user by public key
  public function findByPublicKey(string $public_key)
  {
    $data = $this->database->selectOne($this->table,['public_key' => $public_key]);
    if ($data === null)
      return null;
    else
      return $this->serializer->denormalize($data,User::class);
  }
  
  // Gets all users
  public function findAll(): array
  {
    $data = $this->database->select($this->table,[],'date_modified desc');
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
  
  // Puts a user into the repository
  public function create(User $user): void
  {
    $array = $this->serializer->normalize($user);
    $this->database->insert($this->table,$array);
  }
  
  // Patches a user in the repository
  public function update(User $user): void
  {
    $array = $this->serializer->normalize($user);
    $this->database->update($this->table,$array,['id' => $user->getId()]);
  }
  
  // Deletes a user from the repository
  public function delete(User $user): void
  {
    $this->database->delete($this->table,['id' => $user->getId()]);
  }
}
