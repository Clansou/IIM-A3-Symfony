<?php
 
namespace App\Controller;
 
use App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
 
#[AsController]
final class CreateMediaObjectAction extends AbstractController
{
    public function __invoke(Request $request): Media
    {
        $uploadedFile = $request->files->get('path');
 
        // Rend le champ obligatoire
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"path" is required');
        }
 
        $media = new Media();
        $media->file = $uploadedFile;
 
        return $media;
    }
}