<?php
namespace Devi\Model\Image;

use DateTime;
use Devi\Model\Image;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class ImageDatabaseNormalizer implements NormalizerInterface, DenormalizerInterface
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
      'id' => $object->getId(),
      'name' => $object->getName(),
      'file_name' => $object->getFileName(),
      'file_mime_type' => $object->getFileMimeType(),
      'file_size' => (int)$object->getFileSize(),
      'date_created' => $this->serializer->normalize($object->getDateCreated(),$format,$context),
      'date_modified' => $this->serializer->normalize($object->getDateModified(),$format,$context),
      'public' => (bool)$object->getPublic(),
      'user_id' => (int)$object->getUserId()
    ];
  }
  
  // Return if the normatizer supports denormalization
  public function supportsDenormalization($data, $type, $format = null): bool 
  {
    return $type === Image::class;
  }
  
  // Convert an array to an image
  public function denormalize($array, $type, $format = null, array $context = array()): object 
  {
    if (!$this->supportsDenormalization($array,$type))
      throw new InvalidArgumentException('Could not denormalize the data');
    if (!is_array($array))
      throw new InvalidArgumentException('Expected an array');
    
    return (new Image)
      ->setId($array['id'])
      ->setName($array['name'])
      ->setFileName($array['file_name'])
      ->setFileSize((int)$array['file_size'])
      ->setDateCreated($this->serializer->denormalize($array['date_created'],DateTime::class,$format,$context))
      ->setDateModified($this->serializer->denormalize($array['date_modified'],DateTime::class,$format,$context))
      ->setPublic((bool)$array['public'])
      ->setUserId($array['user_id']);
  }
}
