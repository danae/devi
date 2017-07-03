<?php
namespace Devi\Model\Image;

use Devi\Model\User\User;
use Devi\Utils\Database;
use PDO;
use Symfony\Component\Serializer\Serializer;

class ImageRepository implements ImageRepositoryInterface
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
  
  // Gets an image from the repository
  public function find(string $id)
  {
    $data = $this->database->selectOne($this->table,['id' => $id]);
    if ($data === null)
      return null;
    else
      return $this->serializer->denormalize($data,Image::class);
  }
  
  // Gets all images
  public function findAll(): array
  {
    $data = $this->database->select($this->table,[],'modified_at desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Image::class);
    },$data);
  }
  
  // Gets all images by user
  public function findAllByUser(User $user): array
  {
    $data = $this->database->select($this->table,['user_id' => $user->getId()],'modified_at desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Image::class);
    },$data);
  }
  
  // Gets all public images by user
  public function findAllPublicByUser(User $user): array
  {
    $data = $this->database->select($this->table,['user_id' => $user->getId(),'public' => 1],'modified_at desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Image::class);
    },$data);
  }
  
  // Get all ids as an array
  public function findAllIds(): array
  {
    $st = $this->database->query("SELECT id FROM {$this->table}");
    return $st->fetchAll(PDO::FETCH_NUM);
  }
  
  // Puts an image into the repository
  public function create(Image $image)
  {
    $array = $this->serializer->normalize($image);
    $this->database->insert($this->table,$array);
  }
  
  // Patches an image in the repository
  public function update(Image $image)
  {
    $array = $this->serializer->normalize($image);
    $this->database->update($this->table,$array,['id' => $image->getId()]);
  }
  
  // Deletes an image from the repository
  public function delete(Image $image)
  {
    $this->database->delete($this->table,['id' => $image->getId()]);
  }
}
