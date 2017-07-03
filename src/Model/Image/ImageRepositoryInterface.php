<?php
namespace Devi\Model\Image;

use Devi\Model\User\User;

interface ImageRepositoryInterface
{
  // Gets an image from the repository
  public function find(string $id);
  
  // Gets all images
  public function findAll(): array;
  
  // Gets all images by user
  public function findAllByUser(User $user): array;
  
  // Gets all public images by user
  public function findAllPublicByUser(User $user): array;
  
  // Gets all ids as an array
  public function findAllIds(): array;
  
  // Puts an image into the repository
  public function create(Image $image): void;
  
  // Patches an image in the repository
  public function update(Image $image): void;
  
  // Deletes an image from the repository
  public function delete(Image $image): void;
}
