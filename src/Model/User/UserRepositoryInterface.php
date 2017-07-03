<?php
namespace Devi\Model\User;

interface UserRepositoryInterface
{
  // Gets a user from the repository
  public function find(string $name);
  
  // Gets a user by name
  public function findByName(string $name);
  
  // Gets a user by public key
  public function findByPublicKey(string $public_key);
  
  // Gets all users
  public function findAll(): array;
  
  // Gets all ids as an array
  public function findAllIds(): array;
  
  // Puts a user into the repository
  public function create(User $user);
  
  // Patches a user in the repository
  public function update(User $user);
  
  // Deletes a user from the repository
  public function delete(User $user);
}
