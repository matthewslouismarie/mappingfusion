<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\CategoryModel;
use MF\Model\Slug;
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
            $formData['id'] = $formData['id'] ??  null !== $formData['name'] ? (new Slug($formData['name'], true))->__toString() : null;
            $formErrors = $this->modelValidator->validate($formData, $this->model);

            if (0 === count($formErrors)) {
                $category = new AppObject($formData);
                if (null === $requestedId) {
                    $this->repo->add($category);
                } else {
                    $this->repo->update($category, $requestedId);
                }
                return $this->router->generateRedirect(self::ROUTE_ID, [$category->id]);
            }
        } elseif (null !== $requestedId) {
            $formData = $this->repo->find($requestedId)?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return new Response(body: $this->twig->render('admin_category_form.html.twig', [
            'categories' => $this->repo->findAll(),
            'formData' => $formData,
            'formErrors' => $formErrors,
            'requestedId' => $requestedId,
        ]));
    }

    private function getExistingCategory(array $routeParams): ?AppObject {
        return isset($routeParams[1]) ? $this->repo->findOne($routeParams[1]) : null;
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}