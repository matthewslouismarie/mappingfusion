<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\CategoryModel;
use LM\WebFramework\DataStructures\Slug;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminCategoryController implements ControllerInterface
{
    public function __construct(
        private CategoryModel $model,
        private CategoryRepository $repo,
        private FormFactory $formFactory,
        private ModelValidator $modelValidator,
        private PageFactory $pageFactory,
        private Router $router,
        private SessionManager $sessionManager,
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
            $formData['id'] = $formData['id'] ?? (null !== $formData['name'] ? (new Slug($formData['name'], true))->__toString() : null);
            $formErrors = $this->modelValidator->validate($formData, $this->model);

            if (0 === count($formErrors)) {
                $category = new AppObject($formData);
                if (null === $requestedId) {
                    $this->repo->add($category);
                    $this->sessionManager->addMessage('La catégorie a bien été créée.');
                } else {
                    $this->repo->update($category, $requestedId);
                    $this->sessionManager->addMessage('La catégorie a bien été mise à jour.');
                }
                return $this->router->generateRedirect('admin-manage-category', [$category->id]);
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

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(?AppObject $category): Page
    {
        return $this->pageFactory->create(
            is_null($category) ? 'Nouvelle catégorie' : $category->name,
            self::class,
            is_null($category) ? [] : [$category->id],
            AdminCategoryListController::class,
            isIndexed: false,
        );
    }
}