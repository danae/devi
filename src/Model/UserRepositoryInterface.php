<?php
namespace Devi\Model;

interface UserRepositoryInterface
{
  // Puts a user into the repository
  public function create(User $user): void;
  
  // Gets a user from the repository
  public function retrieve(int $id): User;
  
  // Gets a user by name
  public function retrieveByName(string $name): User;
  
  // Gets a user by public key
  public function retrieveByPublicKey(string $public_key): User;
  
  // Gets all users
  public function retreiveAll(): array;
  
  // Patches a user in the repository
  public function update(User $user): void;
  
  // Deletes a user from the repository
  public function delete(User $user): void;
}
