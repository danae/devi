<?php
namespace Gallerie\Model;

interface UserRepositoryInterface
{
  // Get a user from the repository
  public function get($id);
  
  // Get a user by name
  public function getByName($name);
  
  // Put a user into the repository
  public function put(User $user);
  
  // Patch a user in the repository
  public function patch(User $user);
  
  // Delete a user from the repository
  public function delete(User $user);
}
