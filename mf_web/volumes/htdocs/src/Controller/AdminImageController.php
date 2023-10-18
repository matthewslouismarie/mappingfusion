<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Framework\DataStructures\Filename;
use MF\Framework\Form\Transformer\CheckboxTransformer;
use MF\Framework\Form\Transformer\FileTransformer;
use MF\Session\SessionManager;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class AdminImageController implements ControllerInterface
{
    const ROUTE_ID = 'manage-images';

    private string $uploaded;

    public function __construct(
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
        $this->uploaded = dirname(__FILE__) . '/../../public/uploaded/';
    }

    /**
     * @todo Use form with validation and success messages.
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        if ('POST' === $request->getMethod()) {
            $imgToDelete = $request->getParsedBody()['image-to-delete'] ?? null;
            if (null !== $imgToDelete) {
                $filename = new Filename($imgToDelete);
                $deletion = unlink($this->uploaded . $filename->__toString());

                $smallImgFilename = $this->uploaded . $filename->getFilenameNoExtension() . '.small.webp';
                if (file_exists($smallImgFilename)) {
                    unlink($smallImgFilename);
                }
                $mediumImgFilename = $this->uploaded . $filename->getFilenameNoExtension() . '.medium.webp';
                if (file_exists($mediumImgFilename)) {
                    unlink($mediumImgFilename);
                }
                if ($deletion) {
                    $this->sessionManager->addMessage('Le fichier a été supprimé.');
                }
            } else {
                $createThumbnails = (new CheckboxTransformer('create_thumbnails'))->extractValueFromRequest($request->getParsedBody(), []);
                $transformer = new FileTransformer('images', $createThumbnails);
                
                $transformer->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
                $this->sessionManager->addMessage('Le fichier a bien été ajouté.');
            }
        }

        // $listOfFiles = array_filter(scandir($this->uploaded), fn ($value) => !str_contains($value, '.medium.') && !str_contains($value, '.small.'));
        $listOfFiles = scandir($this->uploaded);
        $images = array_filter($listOfFiles, function ($value) {
            return 1 === preg_match('/^.+\.(jpg)|(jpeg)|(png)|(webp)$/', $value);
        });

        return new Response(body: $this->twig->render('image_management.html.twig', [
            'images' => $images,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}