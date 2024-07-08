<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use MF\Model\BookModel;
use MF\Model\ChapterModel;
use MF\Repository\ChapterRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminChapterController implements ControllerInterface
{
    public function __construct(
        private ChapterRepository $chapterRepository,
        private FormController $formController,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        // @todo Use model to check.
        if (1 !== count($routeParams) && 2 !== count($routeParams)) {
            throw new RequestedResourceNotFound();
        }

        return $this->formController->generateResponse(
            $request,
            $routeParams,
            $routeParams[1] ?? null,
            new ChapterModel(
                new AbstractEntity([
                    'id' => new SlugModel(),
                    'title' => new StringModel(),
                ]),
            ),
            [
                'id' => [
                    'required' => false,
                    'default' => function ($values) {
                        return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                    }
                ]
            ],
            'Il existe déjà un chapitre avec le même ID.',
            $this->chapterRepository,
            'admin_chapter.html.twig',
            function ($formData) {
                return null === $formData ? 'Nouveau chapitre' : $formData['title'];
            },
            'Le chapitre a été créé avec succès.',
            'Le chapitre a été mis à jour avec succès.',
            true,
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}