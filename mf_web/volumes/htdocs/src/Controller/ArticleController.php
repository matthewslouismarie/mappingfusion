<?php

namespace MF\Controller;
use MF\Request;
use MF\TwigService;

class ArticleController
{
    private TwigService $twig;

    public function __construct(
        TwigService $twig,
    ) {
        $this->twig = $twig;
    }

    public function handleArticlePage(Request $request): string {
        return $this->twig->render('article.html.twig');
    }
}