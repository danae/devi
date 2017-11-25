<?php
namespace Devi\Model;

use DateTime;
use InvalidArgumentException;

trait ModifiableTrait
{
  // Variables
  protected $createdAt;
  protected $modifiedAt;
  
  // Management
  public function getCreatedAt(): DateTime
  {
    return $this->createdAt;
  }
  public function setCreatedAt($createdAt): self
  {
    if (is_string($createdAt))
      $this->createdAt = new DateTime($createdAt);
    else if (is_a($createdAt,DateTime::class))
      $this->createdAt = $createdAt;
    else
      throw new InvalidArgumentException('createdAt');
    
    return $this;
  }
  public function getModifiedAt(): DateTime
  {
    return $this->modifiedAt;
  }
  public function setModifiedAt($modifiedAt): self
  {
    if (is_string($modifiedAt))
      $this->modifiedAt = new DateTime($modifiedAt);
    else if (is_a($modifiedAt,DateTime::class))
      $this->modifiedAt = $modifiedAt;
    else
      throw new InvalidArgumentException('modifiedAt');
    
    return $this;
  }
}
