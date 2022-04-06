<?php

namespace App\Controller;

use App\Entity\Blob;
use App\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class CreateBlobAction extends AbstractController
{
    public function __invoke(Request $request, DocumentRepository $documentRepo)
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        // TODO There has to be a better way to do this
        $documentIri = $request->get('document');
        if (!preg_match('#/api/documents/([0-9]+)#', $documentIri, $matches)) {
            throw new BadRequestHttpException(sprintf('"%s" is not a valid document identifier', $documentIri));
        }
        $document = $documentRepo->find($matches[1]);
        if (!$document) {
            throw new BadRequestHttpException(sprintf('document "%s" not found', $documentIri));
        }

        $blob = new Blob();
        $blob->setFile($uploadedFile);
        $blob->setDocument($document);

        return $blob;
    }
}
