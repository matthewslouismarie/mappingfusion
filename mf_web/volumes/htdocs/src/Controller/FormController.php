<?php

namespace MF\Controller;

use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
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
        ?string $requestedId,
        array $formConfig,
        array $routeParams,
        IModel $model,
        IRepository $repository,
        Page $page,
        ServerRequestInterface $request,
        string $idAlreadyTakenMessage,
        string $successfulInsertMessage,
        string $successfulUpdateMessage,
        string $twigFilename,
        array $additionalTwigParams = [],
        bool $alwaysFetchEntity = false,
    ): ResponseInterface {
        // @todo Put formData and formErrors in the same object?
        $formData = null;
        $requestedEntity = ($alwaysFetchEntity && null !== $requestedId) ? $repository->find($requestedId) : null;
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

            $formData = $extractedFromRequest;
            $formErrors = $this->modelValidator->validate($formData, $model);

            if (0 === count($formErrors)) {
                $appObject = new AppObject($formData);
                try {
                    if (null === $requestedId) {
                        $repository->add($appObject);
                        $this->sessionManager->addMessage($successfulInsertMessage);
                        return $this->getSuccessfulRedirect($routeParams, $requestedId, $appObject['id']);
                    } else {
                        $repository->update($appObject, $requestedId);
                        $this->sessionManager->addMessage($successfulUpdateMessage);
                        return $this->getSuccessfulRedirect($routeParams, $requestedId, $appObject['id']);
                    }
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $formErrors['form'][] = $idAlreadyTakenMessage;
                    } else {
                        throw $e;
                    }
                }
            }
        }
        elseif (null !== $requestedId) {
            $requestedEntity = $requestedEntity ?? $repository->find($requestedId);
            if (null === $requestedEntity) {
                throw new RequestedResourceNotFound();
            }
            $formData = $requestedEntity->toArray();
        }

        return $this->twig->respond(
            $twigFilename,
            $page,
            [
                'formData' => $formData,
                'formErrors' => $formErrors,
                'entity' => $requestedEntity,
            ] + $additionalTwigParams,
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