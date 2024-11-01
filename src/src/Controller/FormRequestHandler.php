<?php

namespace MF\Controller;

use Closure;
use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\Exceptions\IllegalUserInputException;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\StringModel;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validation\Validator;
use MF\Repository\IRepository;
use MF\Router;
use MF\TwigService;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormRequestHandler
{
    const DELETE_FORM_ID = '_DELETE_FORM';

    public function __construct(
        private CollectionFactory $collectionFactory,
        private FormFactory $formFactory,
        private Router $router,
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
    }

    /** 
     * Process the submitted form embedded in the request, if present.
     * 
     * This function simplifies the tasks required to extract, prepare, validate
     * and process submitted form data.
     * 
     * @param IRepository $repository The entity's repository.
     * @param ServerRequestInterface $request The HTTP request.
     * @param IFormController $controller The controller tasked with generating
     * the response to the user and persisting the form data after validation.
     * @param string|null $id The ID of the entity requested by the user, if
     * present.
     */
    public function respondToRequest(
        IRepository $repository,
        ServerRequestInterface $request,
        IFormController $controller,
        ?string $id = null,
    ): ResponseInterface {
        // @todo Put formData and formErrors in the same object?
        $formData = null;
        $formErrors = null;
        $form = $this->formFactory->createForm(
            $controller->getFormModel(),
            $controller->getFormConfig(),
        );
        $deleteFormErrors = null;

        if ('POST' === $request->getMethod()) {
            if (null !== $id && key_exists(self::DELETE_FORM_ID, $request->getParsedBody())) {
                
                $deleteFormErrors = $this->processDeleteRequest($request, $id);
                if (0 === count($deleteFormErrors)) {
                    $repository->delete($id);
                    return $controller->respondToDeletion($id);
                }
            }
            else {
                $formData = $form->extractValueFromRequest(
                    $request->getParsedBody(),
                    $request->getUploadedFiles(),
                );
                $formData = $controller->prepareFormData($request, $formData);
    
                $validator = new Validator($controller->getFormModel());
                $formErrors = $validator->validate($formData);
    
                if (0 === count($formErrors)) {
                    $appObject = $this->collectionFactory->createDeepAppObject($formData);
                    try {
                        if (null === $id) {
                            return $controller->respondToInsertion($appObject);
                        } else {
                            return $controller->respondToUpdate($appObject, $id);
                        }
                    } catch (PDOException $e) {
                        if ('23000' === $e->getCode()) {
                            $formErrors['form'][] = $controller->getUniqueConstraintFailureMessage();
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }
        
        if (null !== $id && null === $formData) {
            $entity = $repository->find($id);
            if (null === $entity) {
                throw new RequestedResourceNotFound();
            }
            $formData = $entity->toArray();
        }

        return $controller->respondToNonPersistedRequest($request, $formData, $formErrors, $deleteFormErrors);
    }

    public function isCheckboxChecked(ServerRequestInterface $request, string $checkboxName): bool
    {
        return key_exists($checkboxName, $request->getParsedBody()) && 'on' === $request->getParsedBody()[$checkboxName];
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
        $validator = new Validator($deleteFormModel);
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