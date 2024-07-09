<?php

namespace MF\Controller;

use Closure;
use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Type\ModelValidator;
use MF\Repository\IRepository;
use MF\Router;
use MF\TwigService;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormController
{
    public function __construct(
        private FormFactory $formFactory,
        private ModelValidator $modelValidator,
        private SessionManager $sessionManager,
        private Router $router,
        private TwigService $twig,
    )
    {
    }

    /** 
     * Process the user request and generates an HTTP response.
     * 
     * This function simplifies the tasks repetitively needed to serve the user
     * a page allowing them to see, create or update an entity of a certain
     * model.
     * 
     * @param ?string $requestedId The ID of the entity requested by the user’s
     * request.
     * @param IModel $model The model of entities handled by this controller.
     * @param array $formConfig Any additional configuration for the form.
     * @param string $idAlreadyTakenMessage A message to display to the user if
     * the ID of th entity they are trying to create or update is already taken
     * in the database.
     * @param IRepository $repository The entities’ repository.
     * @param string $twigFilename The filename of the Twig template containing
     * the view.
     * @param callable $htmlPageTitle A closure to generate a title for the
     * page.
     * @param string $successfulInsertMessage A message to display to the user
     * if the entity was successfully added to the database.
     * @param string $successfulUpdateMessage A message to display to the user
     * if the entity was successfully updated in the database.
     * @param bool $alwaysFetchRequestedEntity Whether to fetch the requested
     * entity, even if the request is a POST request.
     * @todo Make $page required.
     */
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        ?string $requestedId,
        IModel $model,
        array $formConfig,
        string $idAlreadyTakenMessage,
        IRepository $repository,
        string $twigFilename,
        Closure $htmlPageTitle,
        string $successfulInsertMessage,
        string $successfulUpdateMessage,
        bool $alwaysFetchEntity = false,
        ?Page $page = null,
    ): ResponseInterface {
        // @todo Use model to check.
        if (1 !== count($routeParams) && 2 !== count($routeParams)) {
            throw new RequestedResourceNotFound();
        }

        // @todo Put formData and formErrors in the same object?
        $formData = null;
        $requestedEntity = $alwaysFetchEntity ? $repository->find($requestedId)?->toArray() : null;
        $formErrors = null;
        $form = $this->formFactory->createForm(
            $model,
            $formConfig,
        );

        if ('POST' === $request->getMethod()) {
            $extractedFromRequest = $form->extractValueFromRequest(
                $request->getParsedBody(),
                $request->getUploadedFiles(),
            );
            if (null === $formData) {
                $formData = $extractedFromRequest;
            } else {
                $formData = $extractedFromRequest + $formData;
            }

            $formErrors = $this->modelValidator->validate($formData, $model);

            if (0 === count($formErrors)) {
                $appObject = new AppObject($formData);
                try {
                    if (null === $requestedId) {
                        $repository->add($appObject);
                        $this->sessionManager->addMessage($successfulInsertMessage);
                        return $this->getSuccessfulRedirect($routeParams, $requestedId, $appObject['id']);
                    } else {
                        $repository->add($appObject);
                        $this->sessionManager->addMessage($successfulUpdateMessage);
                        return $this->getSuccessfulRedirect($routeParams, $requestedId, $appObject['id']);
                    }
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $formErrors['id'][] = $idAlreadyTakenMessage;
                    } else {
                        throw $e;
                    }
                }
            }
        }
        elseif (null !== $requestedId) {
            $formData = $alwaysFetchEntity ? $requestedEntity : $repository->find($requestedId)?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return new Response(
            body: $this->twig->render($twigFilename, [
                'formData' => $formData,
                'formErrors' => $formErrors,
                'pageTitle' => $htmlPageTitle($formData),
                'entity' => $requestedEntity,
                'page' => $page,
            ]),
        );
    }

    private function getSuccessfulRedirect(array $routeParams, ?string $requestedId, string $newEntityId): ResponseInterface {
        if (null === $requestedId) {
            $newRouteParams = array_slice($routeParams, 1);
        } else {
            $newRouteParams = array_slice($routeParams, 1, -1);
        }
        array_push($newRouteParams, $newEntityId);
        return $this->router->generateRedirect($routeParams[0], $newRouteParams);
    }
}