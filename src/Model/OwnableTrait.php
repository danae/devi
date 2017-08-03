<?php
namespace Devi\Model;

trait OwnableTrait
{
  // Variables
  private $userId;
  
  // Management
  public function getUserId(): int
  {
    return $this->userId;
  }
  public function setUserId(int $userId): self
  {
    $this->userId = $userId;
    return $this;
  }
}
