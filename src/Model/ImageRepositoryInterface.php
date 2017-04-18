<?php
namespace Devi\Model;

interface ImageRepositoryInterface
{
  // Puts an image into the repository
  public function create(Image $image): void;
  
  // Gets an image from the repository
  public function retrieve(int $id): Image;
  
  // Gets an image by name
  public function retrieveByName(string $name): Image;
  
  // Gets all images
  public function retrieveAll(): array;
  
  // Gets all images by user
  public function retrieveAllByUser(User $user): array;
  
  // Gets all public images by user
  public function retrieveAllPublicByUser(User $user): array;
  
  // Gets all names as an array
  public function retrieveAllNames(): array;
  
  // Patches an image in the repository
  public function update(Image $image): void;
  
  // Deletes an image from the repository
  public function delete(Image $image): void;
}
