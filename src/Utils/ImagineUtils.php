<?php
namespace Devi\Utils;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ImagineUtils
{
  // Variables
  private $imagine;
  private $mimetypes;
  
  // Constructor
  public function __construct(ImagineInterface $imagine, array $mimetypes)
  {
    $this->imagine = $imagine;
    $this->mimetypes = $mimetypes;
  }
  
  // Creates a response for this blob
  public function respond(ImageInterface $image, string $format, string $file_name): Response
  {
    if (!in_array($format,$this->mimetypes))
      throw new InvalidArgumentException('Could not export to the given format');
    
    // Get the contents of the stream
    $contents = $image->get($format);

    // Create a new response
    $response = new Response($contents);
    
    // Set the response headers
    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $file_name);
    $response->headers->set('Content-Disposition',$disposition);
    $response->headers->set('Content-Type',$this->mimetypes[$format]);
    
    // Return the response
    return $response;
  }
}