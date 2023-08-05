<?php

namespace MF\Controller;

use DomainException;
use GuzzleHttp\Psr7\Response;
use MF\DataStructure\AppObject;
use MF\Enum\Clearance;
use MF\Form\FormFactory;
use MF\Form\FormObjectManager;
use MF\Model\Author;
use MF\Model\AuthorDefinition;
use MF\Model\Slug;
use MF\Repository\AuthorRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorController implements ControllerInterface
{
    const ROUTE_ID = 'manage_author';

    public function __construct(
        private AuthorDefinition $def,
        private AuthorRepository $repo,
        private FormFactory $FormFactory,
        private FormObjectManager $formObjectManager,
        private Router $router,
        private TwigService $twig,
    ) {
    }
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $author = $this->getAuthorFromRequest($routeParams);

        $form = $this->FormFactory->createForm($this->def, formConfig: [
            'id' => [
                'required' => false,
            ]
        ]);

        $formData = null;
        if ('POST' === $request->getMethod()) {
            $formData = $form->extractFormData($request);
            if (!$formData->hasErrors()) {
                $data = $formData->getData();
                $data['id'] = $data['id'] !== null ? $data['id'] : (new Slug($data['name'], true))->__toString();
                $newAuthor = $this->formObjectManager->toAppObject($data, $this->def);
                if (null === $author) {
                    $newId = $this->repo->add($newAuthor);
                    return $this->router->generateRedirect(self::ROUTE_ID, [$newId]);
                } else {
                    $this->repo->update($author->id, $newAuthor);
                    if ($author->id !== $newAuthor->id) {
                        return $this->router->generateRedirect(self::ROUTE_ID, [$newAuthor->id]);
                    }
                }
            }
        } else {
            $formData = $form->generateFormData($author?->toArray() ?? [], false);
        }

        return new Response(
            body: $this->twig->render('author_form.html.twig', [
                'author' => $formData,
            ])
        );
    }

    private function getAuthorFromRequest(array $routeParams): ?AppObject {
        if (isset($routeParams[1])) {
            return $this->repo->findOne($routeParams[1]);
        } else {
            return null;
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}