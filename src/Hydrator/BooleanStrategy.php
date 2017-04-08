<?php
namespace Picturee\Hydrator;

class BooleanStrategy implements Strategy
{
  // Serialize a value in the conversion from object to array
  public function serialize($value)
  {
    return $value;
  }
  
  // Deserialize a value in the conversion from array to object
  public function deserialize($value)
  {
    return (boolean)$value;
  }
}
