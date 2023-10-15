<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Framework\DataStructures\Filename;
use MF\Framework\Form\Transformer\FileTransformer;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class AdminImageController implements ControllerInterface
{
    const ROUTE_ID = 'manage-images';

    public function __construct(
        private TwigService $twig,
        private string $uploaded = '',
    ) {
        $this->uploaded = dirname(__FILE__) . '/../../public/uploaded/';
    }

    /**
     * @todo Use form with validation and success messages.
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        $successes = [];
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
                    $successes[] = 'Le fichier a été supprimé.';
                }
            } else {
                $transformer = new FileTransformer('images');
                $transformer->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            }
        }

        $listOfFiles = array_filter(scandir($this->uploaded), fn ($value) => !str_contains($value, '.medium.') && !str_contains($value, '.small.'));
        $images = array_filter($listOfFiles, function ($value) {
            return 1 === preg_match('/^.+\.(jpg)|(jpeg)|(png)|(webp)$/', $value);
        });

        return new Response(body: $this->twig->render('image_management.html.twig', [
            'images' => $images,
            'successes' => $successes,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}