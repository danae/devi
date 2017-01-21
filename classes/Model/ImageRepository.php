<?php
namespace Gallerie\Model;

use DateTime;
use MeekroDB;

class ImageRepository implements ImageRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  
  // Constructor
  public function __construct(MeekroDB $database, $table)
  {
    $this->database = $database;
    $this->table = $table;
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
  private function toArray(Image $art)
  {
    return [
      'id' => $art->getId(),
      'user_id' => $art->getUserId(),
      'name' => $art->getName(),
      'file_name' => $art->getFileName(),
      'file_mime_type' => $art->getFileMimeType(),
      'file_size' => $art->getFileSize(),
      'date_created' => $art->getDateCreated(),
      'date_modified' => $art->getDateModified(),
      'public' => $art->isPublic()
    ];
  }
  
  // Get an image from the repository
  public function get($id)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->fromArray($result);
  }
  
  // Get an image by name
  public function getByName($name)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->fromArray($result);
  }
  
  // Put an image into the repository
  public function put(Image $art)
  {
    $this->database->insert($this->table,$this->toArray($art));
  }
  
  // Patch an image in the repository
  public function patch(Image $art)
  {
    $this->database->update($this->table,$this->toArray($art),'id = %d',$art->getId());
  }
  
  // Delete an image from the repository
  public function delete(Image $art)
  {
    $this->database->delete($this->table,'id = %d',$art->getId());
  }
  
  // Get all images
  public function getAll()
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_modified DESC");
    return array_map([$this,'fromArray'],$results);
  }
  
  // Get all images by user
  public function getAllByUser(User $user)
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i ORDER BY date_modified DESC",$user->getId());
    return array_map([$this,'fromArray'],$results);
  }
  
  // Get all public images by user
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
