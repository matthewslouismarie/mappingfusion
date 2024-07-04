<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\BookModel;
use MF\Repository\BookRepository;
use MF\Router;
use MF\TwigService;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBookController implements ControllerInterface
{
    public function __construct(
        private BookModel $bookModel,
        private BookRepository $bookRepository,
        private FormFactory $formFactory,
        private ModelValidator $modelValidator,
        private Router $router,
        private SessionManager $sessionManager,
        private TwigService $twig,
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

        // @todo Put formData and formErros in the same object?
        $formData = null;
        $formErrors = null;
        $form = $this->formFactory->createForm($this->bookModel, config: [
            'id' => [
                'required' => false,
            ]
        ]);

        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());

            if ($formData['id'] === null && $formData['title'] !== null) {
                $formData['id'] = (new Slug($formData['title'], true))->__toString();
            }
            $formErrors = $this->modelValidator->validate($formData, $this->bookModel);

            if (0 === count($formErrors)) {
                $appObject = new AppObject($formData);
                if (2 === count($routeParams)) {
                    $this->bookRepository->update($appObject, $routeParams[1]);
                    $this->sessionManager->addMessage('Le tutoriel a été mis à jour avec succès.');
                } else {
                    try {
                        $this->bookRepository->add($appObject);
                        $this->sessionManager->addMessage('Le tutoriel a été créé avec succès.');
                        return $this->router->generateRedirect('admin-tutoriel', [$formData['id']]);
                    } catch (PDOException $e) {
                        if ('23000' === $e->getCode()) {
                            $formErrors['id'][] = 'Il existe déjà un tutoriel avec le même ID.';
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }
        elseif (2 == count($routeParams)) {
            $formData = $this->bookRepository->find($routeParams[1])?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return new Response(
            body: $this->twig->render('admin_book.html.twig', [
                'formData' => $formData,
                'formErrors' => $formErrors,
                'pageTitle' => null === $formData ? 'Nouveau tutoriel' : $formData['title'],
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}