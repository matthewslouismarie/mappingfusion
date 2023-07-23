<?php

namespace MF\Controller;
use DomainException;
use MF\Enum\Clearance;
use MF\Form;
use MF\Model\Category;
use MF\Model\SlugFilename;
use MF\Repository\ArticleRepository;
use MF\HttpBridge\Session;
use MF\Model\Article;
use GuzzleHttp\Psr7\Response;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryAdminController implements ControllerInterface
{
    const ROUTE_ID = 'manage_category';

    private Form $form;

    private CategoryRepository $repo;

    private Router $router;

    private Session $session;

    private TwigService $twig;

    public function __construct(
        Form $form,
        CategoryRepository $repo,
        Router $router,
        Session $session,
        TwigService $twig,
    ) {
        $this->form = $form;
        $this->repo = $repo;
        $this->router = $router;
        $this->session = $session;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        $category = $this->getEntityFromRequest($request);

        if ('POST' === $request->getMethod()) {
            if (isset($request->getQueryParams()['id'])) {
                $this->repo->update($request->getQueryParams()['id'], $category);
            } else {
                $this->repo->add($category);
            }
        }

        if (null !== $category && (!isset($request->getQueryParams()['id']) || $category->getId()->__toString() !== $request->getQueryParams()['id'])) {
            return $this->router->generateRedirect(self::ROUTE_ID, ['id' => $category->getId()]);
        }

        return new Response(body: $this->twig->render('category_form.html.twig', [
            'entity' => $category?->toArray(),
        ]));
    }

    private function getEntityFromRequest(ServerRequestInterface $request): ?Category {
        if ('POST' === $request->getMethod()) {
            $data = $this->form->nullifyEmptyStrings($request->getParsedBody());
            return Category::fromArray($data);
        } elseif (isset($request->getQueryParams()['id'])) {
            $category = $this->repo->find($request->getQueryParams()['id']);
            if (null === $category) {
                throw new DomainException();
            }
            return $category;
        } else {
            return null;
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}