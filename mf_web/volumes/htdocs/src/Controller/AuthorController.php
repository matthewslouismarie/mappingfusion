<?php

namespace MF\Controller;

use DomainException;
use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Form;
use MF\Model\Author;
use MF\Repository\AuthorRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorController implements ControllerInterface
{
    const ROUTE_ID = 'manage_author';

    public function __construct(
        private Form $form,
        private TwigService $twig,
        private Router $router,
        private AuthorRepository $repo,
    ) {
    }
    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        $author = $this->getAuthorFromRequest($request);

        if (null !== $author && (!isset($request->getQueryParams()['id']) || $author->getId() !== $request->getQueryParams()['id'])) {
            return new Response(302, ['Location' => $this->router->generateUrl('manage_author', ['id' => strval($author->getId())])]);
        }

        return new Response(
            body: $this->twig->render('author_form.html.twig', [
                'author' => $author?->toArray(),
            ])
        );
    }

    private function getAuthorFromRequest(ServerRequestInterface $request): ?Author {
        if ('POST' === $request->getMethod()) {
            $data = $this->form->nullifyEmptyStrings($request->getParsedBody());

            if (isset($request->getQueryParams()['id'])) {
                $author = Author::fromArray($data);
                $this->repo->update($request->getQueryParams()['id'], $author);
                return $author;
            } else {
                $author = Author::fromArray($data);
                $this->repo->add($author);
                return $author;
            }
        } elseif (isset($request->getQueryParams()['id'])) {
            $author = $this->repo->find($request->getQueryParams()['id']);
            if (null === $author) {
                throw new DomainException();
            }
            return $author;
        } else {
            return null;
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}