<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\DataStructure\AppObject;
use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Framework\Form\FormFactory;
use MF\Model\AuthorModel;
use MF\Model\Slug;
use MF\Repository\AuthorRepository;
use MF\Router;
use MF\TwigService;
use OutOfBoundsException;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorController implements ControllerInterface
{
    const ROUTE_ID = 'manage_author';

    public function __construct(
        private AppObjectFactory $appObjectFactory,
        private AuthorModel $model,
        private AuthorRepository $repo,
        private FormFactory $FormFactory,
        private Router $router,
        private TwigService $twig,
    ) {
    }
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $existingAuthor = $this->getAuthorFromRequest($routeParams);
        $formData = $existingAuthor;
        $formErrors = [];

        $form = $this->FormFactory->createForm($this->model, formConfig: [
            'id' => [
                'required' => false,
            ]
        ]);

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFromRequest($request->getParsedBody());
            $formData = $submission->getContent();
            $formErrors = $submission->getErrors();

            if (!$submission->hasErrors()) {
                $formData['id'] = $formData['id'] !== null ? $formData['id'] : (new Slug($formData['name'], true))->__toString();
                try {
                    if (null === $existingAuthor) {
                        $this->repo->add($formData);
                        return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                    } else {
                        $this->repo->update($formData, $existingAuthor->id);
                        if ($existingAuthor->id !== $formData['id']) {
                            return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                        }
                    }
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $formErrors['id'][] = 'Il existe déjà un auteur avec le même ID.';
                    } else {
                        throw $e;
                    }
                }
            }
        }

        return new Response(
            body: $this->twig->render('author_form.html.twig', [
                'formData' => $formData,
                'formErrors' => $formErrors,
            ])
        );
    }

    /**
     * @throws NotFoundException If no author bears the specified ID.
     */
    private function getAuthorFromRequest(array $routeParams): ?AppObject {
        try {
            if (key_exists(1, $routeParams)) {
                return $this->repo->findOne($routeParams[1]);
            } else {
                return null;
            }
        } catch (OutOfBoundsException $e) {
            throw new NotFoundException();
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}