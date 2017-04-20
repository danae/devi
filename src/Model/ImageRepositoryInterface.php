<?php
namespace Devi\Model;

interface ImageRepositoryInterface
{
  // Gets an image from the repository
  public function find(int $id): Image;
  
  // Gets an image by name
  public function findByName(string $name): Image;
  
  // Gets all images
  public function findAll(): array;
  
  // Gets all images by user
  public function findAllByUser(User $user): array;
  
  // Gets all public images by user
  public function findAllPublicByUser(User $user): array;
  
  // Gets all names as an array
  public function findAllNames(): array;
  
  // Puts an image into the repository
  public function create(Image $image): void;
  
  // Patches an image in the repository
  public function update(Image $image): void;
  
  // Deletes an image from the repository
  public function delete(Image $image): void;
}
