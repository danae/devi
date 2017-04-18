<?php
namespace Devi\Serializer;

interface Strategy
{
  // Serialize a value in the conversion from object to array
  public function serialize($value);
  
  // Deserialize a value in the conversion from array to object
  public function deserialize($value);
}
