<?php

namespace MF\Controller;

use Closure;
use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\Exceptions\IllegalUserInputException;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\StringModel;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validator\ModelValidator;
use MF\Repository\IRepository;
use MF\Router;
use MF\TwigService;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormController
{
    const DELETE_FORM_ID = '_DELETE_FORM';

    public function __construct(
        private FormFactory $formFactory,
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
     * @param ?string $id The ID of the entity requested by the user’s
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
        IModel $model,
        IRepository $repository,
        Page $page,
        ServerRequestInterface $request,
        Closure $getSuccessfulRedirect,
        string $twigFilename,
        ?AppObject $entity = null,
        ?string $id = null,
        ?string $redirectAfterDeletion = null,
        array $addBeforeCreateOrUpdate = [],
        array $formConfig = [],
        array $twigAdditionalParams = [],
        string $idAlreadyTakenMessage = 'Cet identifiant est déjà pris.',
        string $successfulInsertMessage = 'L’objet a bien été ajouté.',
        string $successfulUpdateMessage = 'L’objet a bien été mis à jour.',
    ): ResponseInterface {
        // @todo Put formData and formErrors in the same object?
        $formData = null;
        $formErrors = null;
        $form = $this->formFactory->createForm(
            $model,
            $formConfig,
        );
        $deleteFormErrors = null;

        if ('POST' === $request->getMethod()) {
            if (isset($request->getParsedBody()[self::DELETE_FORM_ID])) {
                $deleteFormErrors = $this->processDeleteRequest($request, $id);
                if (0 === count($deleteFormErrors)) {
                    $repository->delete($id);
                    $this->sessionManager->addMessage('La suppression a bien été effectuée.');
                    return new Response(302, ['Location' => $redirectAfterDeletion]);
                }
            }
            else {
                $formData = $form->extractValueFromRequest(
                    $request->getParsedBody(),
                    $request->getUploadedFiles(),
                );
    
                $validator = new ModelValidator($model);
                $formErrors = $validator->validate($formData, $model);
    
                if (0 === count($formErrors)) {
                    if (null !== $entity) {
                        $formData += $entity->toArray();
                    }
                    $formData += $addBeforeCreateOrUpdate;
                    $appObject = new AppObject($formData);
                    try {
                        if (null === $id) {
                            $repository->add($appObject);
                            $this->sessionManager->addMessage($successfulInsertMessage);
                            return $getSuccessfulRedirect($appObject);
                        } else {
                            $repository->update($appObject, $id);
                            $this->sessionManager->addMessage($successfulUpdateMessage);
                            return $getSuccessfulRedirect($appObject);
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
        }
        
        if (null !== $id && null === $formData) {
            $entity = $entity ?? $repository->find($id);
            if (null === $entity) {
                throw new RequestedResourceNotFound();
            }
            $formData = $entity->toArray();
        }

        return $this->twig->respond(
            $twigFilename,
            $page,
            [
                'formData' => $formData,
                'formErrors' => $formErrors,
                'entity' => $entity,
                'deleteFormErrors' => $deleteFormErrors,
            ] + $twigAdditionalParams,
        );
    }

    private function processDeleteRequest(ServerRequestInterface $request, ?string $id): array
    {
        $deleteFormModel = new EntityModel(
            'delete-form',
            [
                self::DELETE_FORM_ID => new StringModel(),
            ],
        );
        $deleteForm = $this->formFactory->createForm(
            $deleteFormModel,
        );
        $deleteFormData = $deleteForm->extractValueFromRequest(
            $request->getParsedBody(),
            $request->getUploadedFiles(),
        );
        $validator = new ModelValidator($deleteFormModel);
        $deleteFormErrors = $validator->validate($deleteFormData, $deleteFormModel);
        if (null === $id) {
            throw new IllegalUserInputException();
        }
        elseif ("Supprimer $id" !== $deleteFormData[self::DELETE_FORM_ID]) {
            $deleteFormErrors[self::DELETE_FORM_ID][] = "Veuillez rentrer « Supprimer {$id} » si vous voulez le supprimer.";
        }

        return $deleteFormErrors;
    }
}