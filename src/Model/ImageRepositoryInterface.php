<?php
namespace Devi\Model;

interface ImageRepositoryInterface
{
  // Gets an image from the repository
  public function get(int $id): Image;
  
  // Gets an image by name
  public function getByName(string $name): Image;
  
  // Puts an image into the repository
  public function put(Image $image): void;
  
  // Patches an image in the repository
  public function patch(Image $image): void;
  
  // Deletes an image from the repository
  public function delete(Image $image): void;
  
  // Gets all images
  public function getAll(): array;
  
  // Gets all images by user
  public function getAllByUser(User $user): array;
  
  // Gets all public images by user
  public function getAllPublicByUser(User $user): array;
  
  // Gets all names as an array
  public function getAllNames(): array;
}
