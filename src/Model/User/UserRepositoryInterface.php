<?php
namespace Devi\Model\User;

interface UserRepositoryInterface
{
  // Get a user from the repository
  public function find(string $name): User;
  
  // Get a user by name
  public function findByName(string $name): User;
  
  // Get a user by public key
  public function findByPublicKey(string $public_key): User;
  
  // Get all users
  public function findAll(): array;
  
  // Get all ids as an array
  public function findAllIds(): array;
  
  // Create a user in the repository
  public function create(User $user);
  
  // Update a user in the repository
  public function update(User $user);
  
  // Deletes a user from the repository
  public function delete(User $user);
}
