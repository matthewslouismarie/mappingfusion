<?php

namespace MF\Controller;
use DateTimeImmutable;
use MF\Repository\ArticleRepository;
use MF\HttpBridge\Session;
use MF\Model\Article;
use MF\Request;
use MF\TwigService;

class ArticleController
{
    private ArticleRepository $repo;

    private Session $session;

    private TwigService $twig;

    public function __construct(
        ArticleRepository $repo,
        Session $session,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->session = $session;
        $this->twig = $twig;
    }

    public function handleArticlePage(Request $request): string {
        if (null === $this->session->getCurrentMemberUsername()) {
            return $this->twig->render('error.html.twig', [
                'title' => 'Connection requise',
                'message' => 'Vous n’êtes pas connectés.',
            ]);
        }

        $articleData = [
            'p_title' => null,
            'p_content' => null,
        ];
        if ('POST' === $request->getMethod()) {
            $article = Article::fromArray($request->getParsedBody() + [
                'p_author' => $this->session->getCurrentMemberUsername(),
                'p_creation_datetime' => new DateTimeImmutable(),
                'p_last_update_datetime' => new DateTimeImmutable(),
            ]);
            $this->repo->add($article);
        } elseif (null !== $request->getQueryParams()['id']) {
            $article = $this->repo->find($request->getQueryParams()['id']);
            $articleData = $article->toArray();
            print_r($articleData);
        }
        return $this->twig->render('article.html.twig', [
            'article' => $articleData,
        ]);
    }
}