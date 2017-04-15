<?php
namespace Devi\Model;

use DateTime;
use Devi\Hydrator\BooleanStrategy;
use Devi\Hydrator\DateTimeStrategy;
use Devi\Hydrator\Hydrator;
use Devi\Hydrator\IntegerStrategy;
use MeekroDB;

class ImageRepository implements ImageRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $storage;
  private $hydrator;
  
  // Constructor
  public function __construct(MeekroDB $database, $table, ImageStorageInterface $storage)
  {
    $this->database = $database;
    $this->table = $table;
    $this->storage = $storage;
    $this->hydrator = (new Hydrator)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('file_size',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy)
      ->withStrategy('public',new BooleanStrategy)
      ->withStrategy('user_id',new IntegerStrategy);
  }
  
  // Gets an image from the repository
  public function get(int $id): Image
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->hydrator->deserialize($result,new Image);
  }
  
  // Gets an image by name
  public function getByName(string $name): Image
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->hydrator->deserialize($result,new Image);
  }
  
  // Puts an image into the repository
  public function put(Image $image): void
  {
    $array = $this->hydrator->serialize($image);
    $this->database->insert($this->table,$array);
  }
  
  // Patches an image in the repository
  public function patch(Image $image): void
  {
    $array = $this->hydrator->serialize($image->setDateModified(new DateTime));
    $this->database->update($this->table,$array,'id = %d',$image->getId());
  }
  
  // Deletes an image from the repository
  public function delete(Image $image): void
  {
    $this->database->delete($this->table,'id = %d',$image->getId());
  }
  
  // Gets all images
  public function getAll(): array
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_modified DESC");
    return array_map(function($result) {
      return $this->hydrator->deserialize($result,new Image);
    },$results);
  }
  
  // Gets all images by user
  public function getAllByUser(User $user): array
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i ORDER BY date_modified DESC",$user->getId());
    return array_map(function($result) {
      return $this->hydrator->deserialize($result,new Image);
    },$results);
  }
  
  // Gets all public images by user
  public function getAllPublicByUser(User $user): array
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i AND public = 1 ORDER BY date_modified DESC",$user->getId());
    return array_map(function($result) {
      return $this->hydrator->deserialize($result,new Image);
    },$results);
  }
  
  // Get all names as an array
  public function getAllNames(): array
  {
    return $this->database->queryFirstColumn("SELECT name FROM {$this->table}");
  }
}
