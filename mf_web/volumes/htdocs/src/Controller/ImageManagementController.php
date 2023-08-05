<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Model\SlugFilename;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class ImageManagementController implements ControllerInterface
{
    const ROUTE_ID = 'manage-images';

    public function __construct(
        private TwigService $twig,
        private string $uploaded = '',
    ) {
        $this->uploaded = dirname(__FILE__) . '/../../public/uploaded/';
    }

    /**
     * @todo Unified upload system. (including accepted files)
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        $successes = [];
        if ('POST' === $request->getMethod()) {
            $imgToDelete = $request->getParsedBody()['image-to-delete'];
            if (null !== $imgToDelete) {
                $deletion = unlink($this->uploaded . $imgToDelete);
                if ($deletion) {
                    $successes[] = 'Le fichier a été supprimé.';
                }
            } else {
                $uploadedImages = $request->getUploadedFiles();
                foreach ($uploadedImages['images'] as $img) {
                    $filename = new SlugFilename(rand(1000, 9999) . $img->getClientFilename(), true);
                    $img->moveTo($this->uploaded . $filename->__toString());
                }
            }
        }

        $listOfFiles = scandir($this->uploaded);
        $images = array_filter($listOfFiles, function ($value) {
            return 1 === preg_match('/^.+\.(jpg)|(jpeg)|(png)$/', $value);
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