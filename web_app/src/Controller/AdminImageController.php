<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Filename;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\File\FileService;
use LM\WebFramework\Form\Transformer\FileTransformer;
use LM\WebFramework\Session\SessionManager;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class AdminImageController implements IController, SinglePageOwner
{
    public function __construct(
        private Configuration $configuration,
        private FileService $fileService,
        private PageFactory $pageFactory,
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
    }

    /**
     * @todo Use form with validation and success messages.
     * @todo Image deletion logic should be moved elsewhere. In ImageRepository?
     */
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $uploadPath = $this->configuration->getPathOfUploadedFiles();

        if ('POST' === $request->getMethod()) {
            $imgToDelete = $request->getParsedBody()['image-to-delete'] ?? null;
            if (null !== $imgToDelete) {
                $filename = new Filename($imgToDelete);
                $deletion = unlink($uploadPath . '/' . $filename->__toString());

                $smallImgFilename = $uploadPath . '/' . $filename->getFilenameNoExtension() . '.small.webp';
                if (file_exists($smallImgFilename)) {
                    unlink($smallImgFilename);
                }

                $mediumImgFilename = $uploadPath . '/' . $filename->getFilenameNoExtension() . '.medium.webp';
                if (file_exists($mediumImgFilename)) {
                    unlink($mediumImgFilename);
                }

                if ($deletion) {
                    $this->sessionManager->addMessage('Le fichier a été supprimé.');
                }
            } else {
                $transformer = new FileTransformer($this->configuration->getPathOfUploadedFiles(), 'images', false);
                
                $transformer->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
                $this->sessionManager->addMessage('Le fichier a bien été ajouté.');
            }
        }

        $images = $this->fileService->getUploadedImages(false);

        if (isset($routeParams[0])) {
            switch ($routeParams[0]) {
            case 'par-date':
                usort(
                    $images, function ($a, $b) {
                            $aDate = filemtime($this->configuration->getPathOfUploadedFiles() . '/' . $a);
                            $bDate = filemtime($this->configuration->getPathOfUploadedFiles() . '/' . $b);
                            return $bDate - $aDate;
                    }
                );
                break;
                
            case 'par-date-inversee':
                usort(
                    $images, function ($a, $b) {
                            $aDate = filemtime($this->configuration->getPathOfUploadedFiles() . '/' . $a);
                            $bDate = filemtime($this->configuration->getPathOfUploadedFiles() . '/' . $b);
                            return $aDate - $bDate;
                    }
                );
                break;
            }
        }

        return $this->twig->respond(
            'admin_image_management.html.twig',
            $this->getPage(),
            [
                'images' => $images,
            ],
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            name:  'Gestion des images',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
            isIndexed: false,
        );
    }
}