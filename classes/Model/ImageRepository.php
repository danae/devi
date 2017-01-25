<?php
namespace Gallerie\Model;

use DateTime;
use MeekroDB;

class ImageRepository implements ImageRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $storage;
  
  // Constructor
  public function __construct(MeekroDB $database, $table, ImageStorageInterface $storage)
  {
    $this->database = $database;
    $this->table = $table;
    $this->storage = $storage;
  }
  
  // Convert an image from an associative array
  private function fromArray($array)
  {
    if ($array == null)
      return null;
    
    return (new Image)
      ->withId((int)$array['id'])
      ->withUserId((int)$array['user_id'])
      ->withName($array['name'])
      ->withFileName($array['file_name'])    
      ->withFileMimeType($array['file_mime_type'])
      ->withFileSize((int)$array['file_size'])
      ->withDateCreated(new DateTime($array['date_created']))
      ->withDateModified(new DateTime($array['date_modified']))
      ->withPublic((bool)$array['public']);
  }
  
  // Convert an image to an associative array
  private function toArray(Image $image)
  {
    return [
      'id' => $image->getId(),
      'user_id' => $image->getUserId(),
      'name' => $image->getName(),
      'file_name' => $image->getFileName(),
      'file_mime_type' => $image->getFileMimeType(),
      'file_size' => $image->getFileSize(),
      'date_created' => $image->getDateCreated(),
      'date_modified' => $image->getDateModified(),
      'public' => $image->isPublic()
    ];
  }
  
  // Gets an image from the repository
  public function get($id)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->fromArray($result);
  }
  
  // Gets an image by name
  public function getByName($name)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->fromArray($result);
  }
  
  // Puts an image into the repository
  public function put(Image $image)
  {
    $this->database->insert($this->table,$this->toArray($image));
  }
  
  // Patches an image in the repository
  public function patch(Image $image)
  {
    $this->database->update($this->table,$this->toArray($image),'id = %d',$image->getId());
  }
  
  // Deletes an image from the repository
  public function delete(Image $image)
  {
    $this->database->delete($this->table,'id = %d',$image->getId());
  }
  
  // Gets all images
  public function getAll()
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_modified DESC");
    return array_map([$this,'fromArray'],$results);
  }
  
  // Gets all images by user
  public function getAllByUser(User $user)
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i ORDER BY date_modified DESC",$user->getId());
    return array_map([$this,'fromArray'],$results);
  }
  
  // Gets all public images by user
  public function getAllPublicByUser(User $user)
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i AND public = 1 ORDER BY date_modified DESC",$user->getId());
    return array_map([$this,'fromArray'],$results);
  }
  
  // Get all names as an array
  public function getAllNames()
  {
    return $this->database->queryFirstColumn("SELECT name FROM {$this->table}");
  }
}
