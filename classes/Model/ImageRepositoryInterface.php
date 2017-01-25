<?php
namespace Gallerie\Model;

interface ImageRepositoryInterface
{
  // Gets an image from the repository
  public function get($id);
  
  // Gets an image by name
  public function getByName($name);
  
  // Puts an image into the repository
  public function put(Image $image);
  
  // Patches an image in the repository
  public function patch(Image $image);
  
  // Deletes an image from the repository
  public function delete(Image $image);
  
  // Gets all images
  public function getAll();
  
  // Gets all images by user
  public function getAllByUser(User $user);
  
  // Gets all public images by user
  public function getAllPublicByUser(User $user);
  
  // Gets all names as an array
  public function getAllNames();
}
