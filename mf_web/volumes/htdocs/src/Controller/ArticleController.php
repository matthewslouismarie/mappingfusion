<?php

namespace MF\Controller;
use DomainException;
use MF\Form;
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

class ArticleController implements ControllerInterface
{
    const ROUTE_ID = 'manage_article';

    private CategoryRepository $catRepo;

    private Form $form;

    private ArticleRepository $repo;

    private Router $router;

    private Session $session;

    private TwigService $twig;

    public function __construct(
        CategoryRepository $catRepo,
        Form $form,
        ArticleRepository $repo,
        Router $router,
        Session $session,
        TwigService $twig,
    ) {
        $this->catRepo = $catRepo;
        $this->form = $form;
        $this->repo = $repo;
        $this->router = $router;
        $this->session = $session;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {    
        $article = $this->getEntityFromRequest($request);

        if (null !== $article && (!isset($request->getQueryParams()['id']) || $article->getId() !== $request->getQueryParams()['id'])) {
            return new Response(302, ['Location' => $this->router->generateUrl(self::ROUTE_ID, ['id' => strval($article->getId())])]);
        }

        return new Response(body: $this->twig->render('article.html.twig', [
            'article' => $article?->toArray(),
            'categories' => $this->catRepo->findAll(),
        ]));
    }

    private function getEntityFromRequest(ServerRequestInterface $request): ?Article {
        if ('POST' === $request->getMethod()) {
            $data = $this->form->nullifyEmptyStrings($request->getParsedBody());
    
            $uploadedFile = $request->getUploadedFiles()['p_cover_uploaded_file'];
            $wasFileUploaded = null !== $uploadedFile->getSize() && $uploadedFile->getSize() > 0;
            if ($wasFileUploaded) {
                $filename = new SlugFilename($uploadedFile->getClientFilename());
                $uploadedFile->moveTo(dirname(__FILE__) . "/../../public/uploaded/" . $filename->__toString());
                $data['p_cover_filename'] = $filename->__toString();
            }
            $data['p_is_featured'] = isset($data['p_is_featured']);

            if (isset($request->getQueryParams()['id'])) {
                $article = Article::fromArray($data);
                $this->repo->updateArticle($request->getQueryParams()['id'], $article, $wasFileUploaded);
                return $article;
            } else {
                $article = Article::fromArray([
                    'p_author_username' => $this->session->getCurrentMemberUsername(),
                    'p_creation_datetime' => "now",
                    'p_last_update_datetime' => "now",
                ] + $data);
                $this->repo->addNewArticle($article);
                return $article;
            }
        } elseif (isset($request->getQueryParams()['id'])) {
            $article = $this->repo->find($request->getQueryParams()['id']);
            if (null === $article) {
                throw new DomainException();
            }
            return $article;
        } else {
            return null;
        }
    }

    public function getAccessControl(): int {
        return 1;
    }
}