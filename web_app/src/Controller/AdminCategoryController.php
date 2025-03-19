<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validation\Validator;
use MF\Model\CategoryModelFactory;
use LM\WebFramework\DataStructures\Slug;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminCategoryController implements IController
{
    public function __construct(
        private CategoryModelFactory $categoryModelFactory,
        private CategoryRepository $repo,
        private FormFactory $formFactory,
        private PageFactory $pageFactory,
        private Router $router,
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {   
        $requestedId = $routeParams[0] ?? null;

        $formData = null;
        $formErrors = null;

        $model = $this->categoryModelFactory->create();
        $form = $this->formFactory->createForm(
            $model, config: [
            'id' => [
                'required' => false,
            ],
            ]
        );

        if ('POST' === $request->getMethod()) {
            $formData = $form->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
            $formData['id'] = $formData['id'] ?? (null !== $formData['name'] ? (new Slug($formData['name'], true))->__toString() : null);
            $validator = new Validator($model);
            $formErrors = $validator->validate($formData, $model);

            if (0 === count($formErrors)) {
                $category = new AppObject($formData);
                if (null === $requestedId) {
                    $this->repo->add($category);
                    $this->sessionManager->addMessage('La catégorie a bien été créée.');
                } else {
                    $this->repo->update($category, $requestedId);
                    $this->sessionManager->addMessage('La catégorie a bien été mise à jour.');
                }
                return $this->router->generateRedirect('admin/categories', [$category['id']]);
            }
        } elseif (null !== $requestedId) {
            $formData = $this->repo->find($requestedId)?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return $this->twig->respond(
            'admin_category_form.html.twig',
            $this->getPage(is_null($requestedId) ? null : new AppObject($formData)),
            [
                'categories' => $this->repo->findAll(),
                'formData' => $formData,
                'formErrors' => $formErrors,
                'requestedId' => $requestedId,
            ],
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(?AppObject $category): Page
    {
        return $this->pageFactory->create(
            is_null($category) ? 'Nouvelle catégorie' : $category['name'],
            self::class,
            is_null($category) ? [] : [$category['id']],
            AdminCategoryListController::class,
            isIndexed: false,
        );
    }
}