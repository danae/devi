<?php
namespace Devi\Model\User;

interface UserRepositoryInterface
{
  // Gets a user from the repository
  public function find(int $id): User;
  
  // Gets a user by name
  public function findByName(string $name): User;
  
  // Gets a user by public key
  public function findByPublicKey(string $public_key): User;
  
  // Gets all users
  public function findAll(): array;
  
  // Puts a user into the repository
  public function create(User $user): void;
  
  // Patches a user in the repository
  public function update(User $user): void;
  
  // Deletes a user from the repository
  public function delete(User $user): void;
}
