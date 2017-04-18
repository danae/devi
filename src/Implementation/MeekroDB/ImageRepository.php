<?php
namespace Devi\Implementation\MeekroDB; 

use DateTime;
use Devi\Model\Image;
use Devi\Model\ImageRepositoryInterface;
use Devi\Model\User;
use Devi\Serializer\BooleanStrategy;
use Devi\Serializer\DateTimeStrategy;
use Devi\Serializer\IntegerStrategy;
use Devi\Serializer\Serializer;
use MeekroDB;

class ImageRepository implements ImageRepositoryInterface
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
      ->withStrategy('file_size',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy)
      ->withStrategy('public',new BooleanStrategy)
      ->withStrategy('user_id',new IntegerStrategy);
  }
  
  // Puts an image into the repository
  public function create(Image $image): void
  {
    $array = $this->serializer->serialize($image);
    $this->database->insert($this->table,$array);
  }
  
  // Gets an image from the repository
  public function retrieve(int $id): Image
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->serializer->deserialize($result,new Image);
  }
  
  // Gets an image by name
  public function retrieveByName(string $name): Image
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->serializer->deserialize($result,new Image);
  }
  
  // Gets all images
  public function retrieveAll(): array
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_modified DESC");
    return array_map(function($result) {
      return $this->serializer->deserialize($result,new Image);
    },$results);
  }
  
  // Gets all images by user
  public function retrieveAllByUser(User $user): array
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i ORDER BY date_modified DESC",$user->getId());
    return array_map(function($result) {
      return $this->serializer->deserialize($result,new Image);
    },$results);
  }
  
  // Gets all public images by user
  public function retrieveAllPublicByUser(User $user): array
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i AND public = 1 ORDER BY date_modified DESC",$user->getId());
    return array_map(function($result) {
      return $this->serializer->deserialize($result,new Image);
    },$results);
  }
  
  // Get all names as an array
  public function retrieveAllNames(): array
  {
    return $this->database->queryFirstColumn("SELECT name FROM {$this->table}");
  }
  
  // Patches an image in the repository
  public function update(Image $image): void
  {
    $array = $this->serializer->serialize($image->setDateModified(new DateTime));
    $this->database->update($this->table,$array,'id = %d',$image->getId());
  }
  
  // Deletes an image from the repository
  public function delete(Image $image): void
  {
    $this->database->delete($this->table,'id = %d',$image->getId());
  }
}
