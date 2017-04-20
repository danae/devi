<?php
namespace Devi\Model\PDO;

use Devi\Model\Image;
use Devi\Model\ImageRepositoryInterface;
use Devi\Model\User;
use Devi\Serializer\BooleanStrategy;
use Devi\Serializer\DateTimeStrategy;
use Devi\Serializer\IntegerStrategy;
use Devi\Serializer\Serializer;
use PDO;

class ImageRepository implements ImageRepositoryInterface
{
  // Variables
  private $pdo;
  private $table;
  private $serializer;
  
  // Constructor
  public function __construct(PDO $pdo, $table)
  {
    $this->pdo = $pdo;
    $this->table = $table;
    $this->serializer = (new Serializer)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('file_size',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy)
      ->withStrategy('public',new BooleanStrategy)
      ->withStrategy('user_id',new IntegerStrategy);
  }
  
  // Gets an image from the repository
  public function find(int $id)
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE id = :id");
    $st->bindValue(':id',$id);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    // Return null if none found
    if (($result = $st->fetch()) === FALSE)
      return null;
    else
      return $this->serializer->deserialize($result,new Image);
  }
  
  // Gets an image by name
  public function findByName(string $name)
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE name = :name");
    $st->bindValue(':name',$name);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    // Return null if none found
    if (($result = $st->fetch()) === FALSE)
      return null;
    else
      return $this->serializer->deserialize($result,new Image);
  }
  
  // Gets all images
  public function findAll(): array
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        ORDER BY date_modified DESC");
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    return array_map(function($el) {
      return $this->serializer->deserialize($el,new Image);
    },$st->fetch());
  }
  
  // Gets all images by user
  public function findAllByUser(User $user): array
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE user_id = :user_id
        ORDER BY date_modified DESC");
    $st->bindValue(':user_id',$user->getId());
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    return array_map(function($el) {
      return $this->serializer->deserialize($el,new Image);
    },$st->fetch());
  }
  
  // Gets all public images by user
  public function findAllPublicByUser(User $user): array
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE user_id = :user_id AND public = 1
        ORDER BY date_modified DESC");
    $st->bindValue(':user_id',$user->getId());
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    return array_map(function($el) {
      return $this->serializer->deserialize($el,new Image);
    },$st->fetch());
  }
  
  // Get all names as an array
  public function findAllNames(): array
  {
    $st = $this->pdo->prepare(
      "SELECT name FROM {$this->table}");
    $st->execute();
    return $st->fetchAll(PDO::FETCH_NUM);
  }
  
  // Puts an image into the repository
  public function create(Image $image): void
  {
    $st = $this->pdo->prepare(
      "INSERT INTO {$this->table}
        (id, name, file_name, file_mime_type, file_size, date_created, date_modified, public, user_id)
        VALUES NULL, :name, :file_name, :file_mime_type, :file_size, :date_created, :date_modified, :public, :user_id");
    $st->execute([
      ':name' => $image->getName(),
      ':file_name' => $image->getFileName(),
      ':file_mime_type' => $image->getFileMimeType(),
      ':file_size' => $image->getFileSize(),
      ':date_created' => $image->getDateCreated(),
      ':date_modified' => $image->getDateModified(),
      ':public' => $image->isPublic(),
      ':user_id' => $image->getUserId()
    ]);
  }
  
  // Patches an image in the repository
  public function update(Image $image): void
  {
    $st = $this->pdo->prepare(
      "UPDATE {$this->table}
        SET name = :name, file_name = :file_name, file_mime_type = :file_mime_type, file_size = :file_size, date_created = :date_created, date_modified = :date_modified, public = :public, user_id = :user_id
        WHERE id = :id");
    $st->execute([
      ':id' => $image->getId(),
      ':name' => $image->getName(),
      ':file_name' => $image->getFileName(),
      ':file_mime_type' => $image->getFileMimeType(),
      ':file_size' => $image->getFileSize(),
      ':date_created' => $image->getDateCreated(),
      ':date_modified' => $image->getDateModified(),
      ':public' => $image->isPublic(),
      ':user_id' => $image->getUserId()
    ]);
  }
  
  // Deletes an image from the repository
  public function delete(Image $image): void
  {
    $st = $this->pdo->prepare(
      "DELETE FROM {$this->table}
        WHERE id = :id");
    $st->bindValue(':id',$image->getId());
    $st->execute();
  }
}
