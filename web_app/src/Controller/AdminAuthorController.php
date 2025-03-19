<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Validation\Validator;
use MF\Model\AuthorModelFactory;
use LM\WebFramework\DataStructures\Slug;
use MF\Repository\AuthorRepository;
use MF\Router;
use MF\TwigService;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminAuthorController implements IController
{
    public function __construct(
        private AuthorModelFactory $authorModelFactory,
        private AuthorRepository $repo,
        private FormFactory $formFactory,
        private PageFactory $pageFactory,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $requestedId = $routeParams[0] ?? null;
        $requestedEntity = null;
        $formData = null;
        $formErrors = null;
        $model = $this->authorModelFactory->create();

        $form = $this->formFactory->createForm(
            $model, config: [
            'id' => [
                'required' => false,
            ]
            ]
        );

        if ('POST' === $request->getMethod()) {
            $formData = $form->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
            $formData['id'] = $formData['id'] === null && $formData['name'] !== null ? (new Slug($formData['name'], true))->__toString() : $formData['id'];
            $validator = new Validator($model);
            $formErrors = $validator->validate($formData, $model);

            if (0 === count($formErrors)) {
                $author = new AppObject($formData); 
                try {
                    if (null === $requestedId) {
                        $this->repo->add($author);
                    } else {
                        $this->repo->update($author, $requestedId);
                    }
                    return $this->router->generateRedirect('admin/auteur', [$formData['id']]);
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $formErrors['id'][] = 'Il existe déjà un auteur avec le même ID.';
                    } else {
                        throw $e;
                    }
                }
            }
        } elseif (null !== $requestedId) {
            $requestedEntity = $this->repo->find($requestedId);
            $formData = $requestedEntity?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return $this->twig->respond(
            'admin_author_form.html.twig',
            $this->getPage($requestedEntity),
            [
                'formData' => $formData,
                'formErrors' => $formErrors,
            ],
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(?AppObject $author): Page
    {
        return $this->pageFactory->create(
            $author['name'] ?? 'Nouvel auteur',
            self::class,
            null === $author ? [] : [$author['id']],
            AdminAuthorListController::class,
            isIndexed: false,
        );
    }
}