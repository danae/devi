<?php
namespace Devi\Hydrator;

use DateTime;

class DateTimeStrategy implements Strategy
{
  // Serialize a value in the conversion from object to array
  public function serialize($value)
  {
    return $value->format(DateTime::ISO8601);
  }
  
  // Deserialize a value in the conversion from array to object
  public function deserialize($value)
  {
    return new DateTime($value);
  }
}
