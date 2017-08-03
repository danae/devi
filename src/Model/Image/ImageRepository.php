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
  
  // Get a image from the repository
  public function find(string $id): Image
  {
    $data = $this->database->selectOne($this->table,['id' => $id]);
    if ($data === null)
      throw new ImageNotFoundException($id);
    else
      return $this->serializer->denormalize($data,Image::class);
  }
  
  // Get all images
  public function findAll(): array
  {
    $data = $this->database->select($this->table,[],'modifiedAt desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Image::class);
    },$data);
  }
  
  // Get all images by user
  public function findAllByUser(User $user): array
  {
    $data = $this->database->select($this->table,['userId' => $user->getId()],'modifiedAt desc');
    return array_map(function($row) {
      return $this->serializer->denormalize($row,Image::class);
    },$data);
  }
  
  // Get all public images by user
  public function findAllPublicByUser(User $user): array
  {
    $data = $this->database->select($this->table,['userId' => $user->getId(),'public' => 1],'modifiedAt desc');
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
  
  // Create a image in the repository
  public function create(Image $image)
  {
    $array = $this->serializer->normalize($image);
    $this->database->insert($this->table,$array);
  }
  
  // Update a image in the repository
  public function update(Image $image)
  {
    $array = $this->serializer->normalize($image);
    $this->database->update($this->table,$array,['id' => $image->getId()]);
  }
  
  // Delete a image from the repository
  public function delete(Image $image)
  {
    $this->database->delete($this->table,['id' => $image->getId()]);
  }
}
