<?php
namespace Devi\Serializer;

use ReflectionClass;
use RuntimeException;

class Serializer
{
  // Variables
  private $strategies = [];
  
  // Adds a strategy
  public function withStrategy($name, Strategy $strategy)
  {
    $this->strategies[$name] = $strategy;
    return $this;
  }
  
  // Removes a strategy
  public function withoutStrategy($name)
  {
    unset($this->strategies[$name]);
    return $this;
  }
  
  // Gets a strategy
  public function getStrategy($name)
  {
    return $this->strategies[$name];
  }
  
  // Returns if a stragegy for the name exists
  public function hasStrategy($name)
  {
    return array_key_exists($name,$this->strategies);
  }
  
  // Converts an object to an array
  public function serialize($object)
  {
    // Check for null
    if ($object === null)
      return [];
    
    // Create a reflector for the object
    $reflector = new ReflectionClass($object);
    
    // Iterate over the properties
    foreach ($reflector->getProperties() as $property)
    {
      // Access the property directly
      $property->setAccessible(true);
      
      // Check if there exists a strategy for this property
      $name = $property->getName();
      if ($this->hasStrategy($name))
        $array[$name] = $this->getStrategy($name)->serialize($property->getValue($object));
      else
        $array[$name] = $property->getValue($object);
    }
    
    // Return the array
    return $array;
  }
  
  // Converts an array into an object
  public function deserialize($array, $object)
  {
    // Check for null
    if ($object === null)
      throw new RuntimeException('object cannot be null');
    if ($array === null)
      return $object;
    
    // Create a reflector for the object
    $reflector = new ReflectionClass($object);
    
    // Iterate over the array
    foreach ($array as $name => $value)
    {
      // Check if the property exists
      if (!$reflector->hasProperty($name))
        continue;

      // Access the property directly
      $property = $reflector->getProperty($name);
      $property->setAccessible(true);
      
      // Check if there exists a strategy for this property
      $name = $property->getName();
      if ($this->hasStrategy($name))
        $property->setValue($object,$this->getStrategy($name)->deserialize($value));
      else
        $property->setValue($object,$value);
    }
    
    // Return the object
    return $object;
  }
}
