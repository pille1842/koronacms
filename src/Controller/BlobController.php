<?php

namespace App\Controller;

use App\Entity\Blob;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class BlobController extends AbstractController
{
    #[Route('/blob/{fileName}/download', 'blob_download')]
    public function download(Blob $blob, DownloadHandler $downloadHandler): Response
    {
        return $downloadHandler->downloadObject($blob, 'file', null, $blob->getOriginalName(), true);
    }

    #[Route('/blob/{fileName}', 'blob_inline')]
    public function inline(Blob $blob, DownloadHandler $downloadHandler): Response
    {
        return $downloadHandler->downloadObject($blob, 'file', null, $blob->getOriginalName(), false);
    }
}
