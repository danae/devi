<?php
namespace Devi\Model;

use Devi\Serializer\BooleanStrategy;
use Devi\Serializer\DateTimeStrategy;
use Devi\Serializer\IntegerStrategy;
use Devi\Serializer\Serializer;
use Devi\Utils\Database;
use Devi\Utils\Hydrator;
use PDO;

class ImageRepository implements ImageRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $serializer;
  
  // Constructor
  public function __construct(Database $database, string $table)
  {
    $this->database = $database
      ->useClass(Image::class)
      ->useStrategy('date_created',[Hydrator::class,'dateTimeStrategy'])
      ->useStrategy('date_modified',[Hydrator::class,'dateTimeStrategy'])
      ->useStrategy('public',[Hydrator::class,'boolStrategy']);
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
    return $this->database->select($this->table,['id' => $id]);
  }
  
  // Gets an image by name
  public function findByName(string $name)
  {
    return $this->database->select($this->table,['name' => $name]);
  }
  
  // Gets all images
  public function findAll(): array
  {
    return $this->database->select($this->table,[],['order by' => 'date_modified desc']);
  }
  
  // Gets all images by user
  public function findAllByUser(User $user): array
  {
    return $this->database->select($this->table,['user_id' => $user->getId()],['order by' => 'date_modified desc']);
  }
  
  // Gets all public images by user
  public function findAllPublicByUser(User $user): array
  {
    return $this->database->select($this->table,['user_id' => $user->getId(),'public' => 1],['order by' => 'date_modified desc']);
  }
  
  // Get all names as an array
  public function findAllNames(): array
  {
    $st = $this->database->prepare(
      "SELECT name FROM {$this->table}");
    $st->execute();
    return $st->fetchAll(PDO::FETCH_NUM);
  }
  
  // Puts an image into the repository
  public function create(Image $image): void
  {
    $st = $this->database->prepare(
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
    $st = $this->database->prepare(
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
    $st = $this->database->prepare(
      "DELETE FROM {$this->table}
        WHERE id = :id");
    $st->bindValue(':id',$image->getId());
    $st->execute();
  }
}
