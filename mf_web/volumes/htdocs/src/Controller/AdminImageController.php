<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
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
                $deletion = unlink($this->uploaded . $imgToDelete);
                if ($deletion) {
                    $successes[] = 'Le fichier a été supprimé.';
                }
            } else {
                $transformer = new FileTransformer('images');
                $transformer->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            }
        }

        $listOfFiles = scandir($this->uploaded);
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