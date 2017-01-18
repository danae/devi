<?php
namespace Gallerie\Model;

interface ArtRepositoryInterface
{
  // Get art from the repository
  public function get($id);
  
  // Get art by name
  public function getByName($name);
  
  // Put art into the repository
  public function put(Art $art);
  
  // Patch art in the repository
  public function patch(Art $art);
  
  // Delete art from the repository
  public function delete(Art $art);
  
  // Get all art by user
  public function getAllByUser(User $user);
  
  // Get all public art by user
  public function getAllPublicByUser(User $user);
}
