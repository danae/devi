<?php
namespace Devi\Model\Album;

use Devi\Model\User\User;

interface AlbumRepositoryInterface
{
  // Get an album from the repository
  public function find(string $id): Album;
  
  // Get all albums
  public function findAll(): array;
  
  // Get all albums by user
  public function findAllByUser(User $user): array;
  
  // Get all public albums by user
  public function findAllPublicByUser(User $user): array;
  
  // Get all ids as an array
  public function findAllIds(): array;
  
  // Create an album in the repository
  public function create(Album $image);
  
  // Update an album in the repository
  public function update(Album $image);
  
  // Delete an album from the repository
  public function delete(Album $image);
}
