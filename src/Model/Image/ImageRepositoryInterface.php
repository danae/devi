<?php
namespace Devi\Model\Image;

use Devi\Model\User\User;

interface ImageRepositoryInterface
{
  // Get an image from the repository
  public function find(string $id);
  
  // Get all images
  public function findAll(): array;
  
  // Get all images by user
  public function findAllByUser(User $user): array;
  
  // Get all public images by user
  public function findAllPublicByUser(User $user): array;
  
  // Get all ids as an array
  public function findAllIds(): array;
  
  // Create a image in the repository
  public function create(Image $image);
  
  // Update a image in the repository
  public function update(Image $image);
  
  // Delete a image from the repository
  public function delete(Image $image);
}
