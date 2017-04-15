<?php
namespace Devi\Model;

interface UserRepositoryInterface
{
  // Gets a user from the repository
  public function get(int $id): User;
  
  // Gets a user by name
  public function getByName(string $name): User;
  
  // Gets a user by public key
  public function getByPublicKey(string $public_key): User;
  
  // Puts a user into the repository
  public function put(User $user): void;
  
  // Patches a user in the repository
  public function patch(User $user): void;
  
  // Deletes a user from the repository
  public function delete(User $user): void;
  
  // Gets all users
  public function getAll(): array;
}
