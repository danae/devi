<?php
namespace Gallerie\Model;

interface ImageRepositoryInterface
{
  // Get an image from the repository
  public function get($id);
  
  // Get an image by name
  public function getByName($name);
  
  // Put an image into the repository
  public function put(Image $art);
  
  // Patch an image in the repository
  public function patch(Image $art);
  
  // Delete an image from the repository
  public function delete(Image $art);
  
  // Get all images
  public function getAll();
  
  // Get all images by user
  public function getAllByUser(User $user);
  
  // Get all public images by user
  public function getAllPublicByUser(User $user);
  
  // Get all names as an array
  public function getAllNames();
}
