<?php

namespace MF\Controller;

use MF\DataStructure\AppObject;
use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Form\FormFactory;
use MF\Model\CategoryModel;
use MF\Model\Slug;
use MF\Session\SessionManager;
use GuzzleHttp\Psr7\Response;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryAdminController implements ControllerInterface
{
    const ROUTE_ID = 'manage_category';

    public function __construct(
        private CategoryRepository $repo,
        private Router $router,
        private SessionManager $session,
        private AppObjectFactory $appObjectFactory,
        private FormFactory $formFactory,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        $existingCategory = $this->getExistingCategory($routeParams);
        $formData = $existingCategory;
        $formErrors = null;

        $model = new CategoryModel();
        $form = $this->formFactory->createForm($model, formConfig: [
            'id' => [
                'required' => false,
            ]
        ]);

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFormData($request->getParsedBody(), $request->getUploadedFiles());
            $formData = $submission->getData();
            $formErrors = $submission->getErrors();

            if (!$submission->hasErrors()) {
                $formData['id'] = $formData['id'] ?? (new Slug($formData['name'], true))->__toString();
                $category = $this->appObjectFactory->create($formData, $model);
                if (null === $existingCategory) {
                    $this->repo->add($category);
                } else {
                    $this->repo->update($category, $existingCategory->id);
                }
                if (null === $existingCategory || $existingCategory->id || $category->id) {
                    return $this->router->generateRedirect(self::ROUTE_ID, [$category->id]);
                }
            }
        }

        return new Response(body: $this->twig->render('category_form.html.twig', [
            'formData' => $formData,
            'formErrors' => $formErrors,
        ]));
    }

    private function getExistingCategory(array $routeParams): ?AppObject {
        return isset($routeParams[1]) ? $this->repo->findOne($routeParams[1]) : null;
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}