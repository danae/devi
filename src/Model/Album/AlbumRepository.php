<?php
namespace Devi\Model\Album;

use Devi\Database\Database;
use Devi\Model\User\User;
use PDO;
use Symfony\Component\Serializer\Serializer;

class AlbumRepository implements AlbumRepositoryInterface
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
  
  // Get an album from the repository
  public function find(string $id): Album
  {
    $data = $this->database->selectOne($this->table,['id' => $id]);
    if ($data === null)
      throw new AlbumNotFoundException($id);
    else
      return $this->serializer->denormalize($data,Album::class);
  }
  
  // Get all albums
  public function findAll(): array
  {
    $data = $this->database->select($this->table,[],'modifiedAt desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Album::class);
    },$data);
  }
  
  // Get all albums by user
  public function findAllByUser(User $user): array
  {
    $data = $this->database->select($this->table,['userId' => $user->getId()],'modifiedAt desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Album::class);
    },$data);
  }
  
  // Get all public albums by user
  public function findAllPublicByUser(User $user): array
  {
    $data = $this->database->select($this->table,['userId' => $user->getId(),'public' => 1],'modifiedAt desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Album::class);
    },$data);
  }
  
  // Get all ids as an array
  public function findAllIds(): array
  {
    $st = $this->database->query("SELECT id FROM {$this->table}");
    return $st->fetchAll(PDO::FETCH_NUM);
  }
  
  // Create an album in the repository
  public function create(Album $album)
  {
    $array = $this->serializer->normalize($album);
    $this->database->insert($this->table,$array);
  }
  
  // Update an album in the repository
  public function update(Album $album)
  {
    $array = $this->serializer->normalize($album);
    $this->database->update($this->table,$array,['id' => $album->getId()]);
  }
  
  // Delete an album from the repository
  public function delete(Album $album)
  {
    $this->database->delete($this->table,['id' => $album->getId()]);
  }
}
