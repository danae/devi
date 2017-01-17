<?php
namespace Gallerie\Application\Art;

use DateTime;
use Gallerie\Application\ApplicationException;
use Gallerie\Art\Art;
use Gallerie\Art\ArtRepository;
use Gallerie\User\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ArtController
{
  // Variables
  private $repo;
  
  // Constructor
  public function __construct(ArtRepository $repo)
  {
    $this->repo = $repo;
  }
  
  // Validate art
  public function validate($art)
  {  
    // Check if the art exists
    if ($art === null)
      throw new ApplicationException('The specified art was not found',404);
  }
  
  // Validate the owner of the art
  public function validateOwner(Art $art, User $authorized)
  {
    // Check if the user is the owner of the art
    if ($art->getUserId() !== $authorized->getId())
      throw new ApplicationException('The specified art cannot be changed by this user',403);
  }
  
  // Validate an uploaded file
  public function validateFile(UploadedFile $file)
  {
    if ($file === null)
      throw new ApplicationException('The request did not contain a file to upload',400);
    if (!$file->isValid())
      throw new ApplicationException('The specified file was not uploaded sucessfully: ' . $file->getErrorMessage(),400);
    if ($file->getClientSize() > $file->getMaxFilesize())
      throw new ApplicationException('The specified file was too large; maximum size is ' . $file->getMaxFilesize(),413);
  }
  
  // Create new art
  public function post(Request $request)
  {  
    // Validate the file
    $file = $request->files->get('file');
    $this->validateFile($file);
  
    // Create the art
    $art = Art::create($file,$request->request->get('user'),$request->request->get('file_name'));
    $this->repo->put($art);
    
    // Return the created art
    return new JsonResponse($art,201);
  }

  // Get existing art
  public function get($art)
  {
    $this->validate($art);
    return new JsonResponse($art);
  }

  // PATCH: Update existing art
  public function patch($art, Request $request)
  {
    // Validate the art
    $this->validate($art);
    $this->validateOwner($art,$request->request->get('user'));
  
    // Replace the fields
    if ($request->request->has('file_name'))
      $art->withFileName($request->request->get('file_name'));
    if ($request->request->has('public'))
      $art->withPublic($request->request->get('public'));
    if ($request->request->has('nsfw'))
      $art->withNSFW($request->request->get('nsfw'));
  
    // Patch the updated art in the database
    $this->repo->patch($art->withDateModified(new DateTime));
  
    // Return the art
    return new JsonResponse($art);
  }

  // Delete existing art
  public function delete($art, Request $request)
  {
    // Validate the art
    $this->validate($art);
    $this->validateOwner($art,$request->request->get('user'));
  
    // Delete the art
    $this->repo->delete($art);
  
    // Return the art
    return new JsonResponse($art);
  }
  
  // Replace the raw data of existing art
  public function postRaw($art, Request $request)
  {
    // Validate the art
    $this->validate($art);
    $this->validateOwner($art,$request->request->get('user'));
  
    // Validate the file
    $file = $request->files->get('file');
    $this->validateFile($file);

    // Replace the art
    $art->replace($file,$request->request->get('file_name'));
    $this->repo->patch($art->withDateModified(new DateTime));
    
    // Return the updated art
    return new JsonResponse($art);
  }
  
  // Get the raw data of existing art
  public function getRaw($art)
  {
    $this->validate($art);
    return $art->raw();
  }
}
