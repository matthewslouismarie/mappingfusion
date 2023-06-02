<?php

namespace MF\Controller;
use DomainException;
use MF\Model\SlugFilename;
use MF\Repository\ArticleRepository;
use MF\HttpBridge\Session;
use MF\Model\Article;
use GuzzleHttp\Psr7\Response;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements ControllerInterface
{
    private ArticleRepository $repo;

    private Router $router;

    private Session $session;

    private TwigService $twig;

    public function __construct(
        ArticleRepository $repo,
        Router $router,
        Session $session,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->router = $router;
        $this->session = $session;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        if (null === $this->session->getCurrentMemberUsername()) {
            return new Response(body: $this->twig->render('error.html.twig', [
                'title' => 'Connection requise',
                'message' => 'Vous n’êtes pas connectés.',
            ]));
        }
    
        $article = $this->getArticleFromRequest($request);

        if (null !== $article && (!isset($request->getQueryParams()['id']) || $article->getId() !== $request->getQueryParams()['id'])) {
            return new Response(302, ['Location' => $this->router->generateUrl('article', ['id' => strval($article->getId())])]);
        }

        return new Response(body: $this->twig->render('article.html.twig', [
            'article' => $article?->toArray(),
        ]));
    }

    private function getArticleFromRequest(ServerRequestInterface $request): ?Article {
        if ('POST' === $request->getMethod()) {
            $data = $request->getParsedBody();

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
                $article = Article::fromArray($data + [
                    'p_author' => $this->session->getCurrentMemberUsername(),
                    'p_creation_datetime' => "now",
                    'p_last_update_datetime' => "now",
                ]);
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