<?php
namespace Gallerie\Model;

interface UserRepositoryInterface
{
  // Gets a user from the repository
  public function get($id);
  
  // Gets a user by name
  public function getByName($name);
  
  // Gets a user by public key
  public function getByPublicKey($public_key);
  
  // Puts a user into the repository
  public function put(User $user);
  
  // Patches a user in the repository
  public function patch(User $user);
  
  // Deletes a user from the repository
  public function delete(User $user);
  
  // Gets all users
  public function getAll();
}
