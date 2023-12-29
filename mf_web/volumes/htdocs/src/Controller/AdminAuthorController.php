<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\AuthorModel;
use MF\Model\Slug;
use MF\Repository\AuthorRepository;
use MF\Router;
use MF\TwigService;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminAuthorController implements ControllerInterface
{
    public function __construct(
        private AuthorModel $model,
        private AuthorRepository $repo,
        private FormFactory $formFactory,
        private ModelValidator $modelValidator,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $requestedId = $routeParams[1] ?? null;
        $formData = null;
        $formErrors = null;

        $form = $this->formFactory->createForm($this->model, config: [
            'id' => [
                'required' => false,
            ]
        ]);

        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formData['id'] = $formData['id'] === null && $formData['name'] !== null ? (new Slug($formData['name'], true))->__toString() : $formData['id'];
            $formErrors = $this->modelValidator->validate($formData, $this->model);

            if (0 === count($formErrors)) {
                $author = new AppObject($formData); 
                try {
                    if (null === $requestedId) {
                        $this->repo->add($author);
                    } else {
                        $this->repo->update($author, $requestedId);
                    }
                    return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $formErrors['id'][] = 'Il existe déjà un auteur avec le même ID.';
                    } else {
                        throw $e;
                    }
                }
            }
        } elseif (null !== $requestedId) {
            $formData = $this->repo->find($requestedId)?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return new Response(
            body: $this->twig->render('admin_author_form.html.twig', [
                'formData' => $formData,
                'formErrors' => $formErrors,
            ])
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}