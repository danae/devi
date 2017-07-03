<?php
namespace Devi\Model\Image;

use Devi\Model\Image;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class ImageOutputNormalizer implements NormalizerInterface
{  
  use SerializerAwareTrait;
  
  // Return if the normatizer supports normalization
  public function supportsNormalization($object, $format = null): bool 
  {
    return $object instanceof Image;
  }
  
  // Convert an image to an array
  public function normalize($object, $format = null, array $context = []) 
  {
    if (!$this->supportsNormalization($object))
      throw new InvalidArgumentException('Could not normalize the data');
    
    return [
      'name' => $object->getName(),
      'file_name' => $object->getFileName(),
      'file_mime_type' => $object->getFileMimeType(),
      'file_size' => (int)$object->getFileSize(),
      'date_created' => $this->serializer->normalize($object->getDateCreated(),$format,$context),
      'date_modified' => $this->serializer->normalize($object->getDateModified(),$format,$context),
      'public' => (bool)$object->getPublic(),
      'user' => (int)$object->getUserId()
    ];
  }
}
