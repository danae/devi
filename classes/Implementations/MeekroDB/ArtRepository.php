<?php
namespace Gallerie\Implementations\MeekroDB;

use DateTime;
use Gallerie\Model\Art;
use Gallerie\Model\ArtRepositoryInterface;
use Gallerie\Model\User;
use MeekroDB;

class ArtRepository implements ArtRepositoryInterface
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
  
  // Convert art from an associative array
  private function fromArray($array)
  {
    if ($array == null)
      return null;
    
    return (new Art)
      ->withId((int)$array['id'])
      ->withUserId((int)$array['user_id'])
      ->withName($array['name'])
      ->withFileName($array['file_name'])    
      ->withFileLocation($array['file_location'])
      ->withFileMimeType($array['file_mime_type'])
      ->withFileSize((int)$array['file_size'])
      ->withDateCreated(new DateTime($array['date_created']))
      ->withDateModified(new DateTime($array['date_modified']))
      ->withPublic((bool)$array['public']);
  }
  
  // Convert art to an associative array
  private function toArray(Art $art)
  {
    return [
      'id' => $art->getId(),
      'user_id' => $art->getUserId(),
      'name' => $art->getName(),
      'file_name' => $art->getFileName(),
      'file_location' => $art->getFileLocation(),
      'file_mime_type' => $art->getFileMimeType(),
      'file_size' => $art->getFileSize(),
      'date_created' => $art->getDateCreated(),
      'date_modified' => $art->getDateModified(),
      'public' => $art->isPublic()
    ];
  }
  
  // Get art from the repository
  public function get($id)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->fromArray($result);
  }
  
  // Get art by name
  public function getByName($name)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->fromArray($result);
  }
  
  // Put art into the repository
  public function put(Art $art)
  {
    $this->database->insert($this->table,$this->toArray($art));
  }
  
  // Patch art in the repository
  public function patch(Art $art)
  {
    $this->database->update($this->table,$this->toArray($art),'id = %d',$art->getId());
  }
  
  // Delete art from the repository
  public function delete(Art $art)
  {
    $this->database->delete($this->table,'id = %d',$art->getId());
  }
  
  // Get all art
  public function getAll()
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE public = 1 ORDER BY date_modified DESC");
    return array_map([$this,'fromArray'],$results);
  }
  
  // Get all art by user
  public function getAllByUser(User $user)
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i ORDER BY date_modified DESC",$user->getId());
    return array_map([$this,'fromArray'],$results);
  }
  
  // Get all public art by user
  public function getAllPublicByUser(User $user)
  {
    $results = $this->database->query("SELECT * from {$this->table} WHERE user_id = %i AND public = 1 ORDER BY date_modified DESC",$user->getId());
    return array_map([$this,'fromArray'],$results);
  }
}
