<?php
namespace Devi\Model\User;

use Devi\Utils\Database;
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
  public function find(int $id): User
  {
    $data = $this->database->select($this->table,['id' => $id]);
    return $this->serializer->denormalize($data,User::class);
  }
  
  // Gets a user by name
  public function findByName(string $name): User
  {
    $data = $this->database->select($this->table,['name' => $name]);
    return $this->serializer->denormalize($data,User::class);
  }
  
  // Gets a user by public key
  public function findByPublicKey(string $public_key): User
  {
    $data = $this->database->select($this->table,['public_key' => $public_key]);
    return $this->serializer->denormalize($data,User::class);
  }
  
  // Gets all users
  public function findAll(): array
  {
    $data = $this->database->select($this->table,[],['order by' => 'date_modified desc']);
    return $this->serializer->denormalize($data,User::class . '[]');
  }
  
  // Puts a user into the repository
  public function create(User $user): void
  {
    $st = $this->database->prepare(
      "INSERT INTO {$this->table}
        (id, name, email, password, public_key, private_key, date_created, date_modified)
        VALUES NULL, :name, :email, :password, :public_key, :private_key, :date_created, :date_modified");
    $st->execute([
      ':name' => $user->getName(),
      ':email' => $user->getEmail(),
      ':password' => $user->getPassword(),
      ':public_key' => $user->getPublicKey(),
      ':private_key' => $user->getPrivateKey(),
      ':date_created' => $user->getDateCreated(),
      ':date_modified' => $user->getDateModified()
    ]);
  }
  
  // Patches a user in the repository
  public function update(User $user): void
  {
    $st = $this->database->prepare(
      "UPDATE {$this->table}
        SET name = :name, email = :email, password = :password, public_key = :public_key, private_key = :private_key, date_created = :date_created, date_modified = :date_modified
        WHERE id = :id");
    $st->execute([
      ':id' => $user->getId(),
      ':name' => $user->getName(),
      ':email' => $user->getEmail(),
      ':password' => $user->getPassword(),
      ':public_key' => $user->getPublicKey(),
      ':private_key' => $user->getPrivateKey(),
      ':date_created' => $user->getDateCreated(),
      ':date_modified' => $user->getDateModified()
    ]);
  }
  
  // Deletes a user from the repository
  public function delete(User $user): void
  {
    $this->database->delete($this->table,['id' => $user->getId()]);
  }
}
