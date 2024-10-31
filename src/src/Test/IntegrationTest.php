<?php

namespace MF\Test;

use LM\WebFramework\DataStructures\KeyName;
use MF\Router;
use MF\Twig\TemplateHelper;
use RuntimeException;

class IntegrationTest implements IUnitTest
{
    public function __construct(
        private Router $router,
        private TemplateHelper $templateHelper,
        private Tester $tester,
    ) {
    }

    public function run(): array
    {
        /**
         * HTTP requests
         */

        $urls = [
            [
                $this->router->generateUrl(''),
                200,
            ],
            [
                $this->router->generateUrl('article', ['nouvel-version-tcm']),
                200,
            ],
            [
                $this->router->generateUrl('article', ['article-with-thumbnail']),
                200,
            ],
            [
                $this->templateHelper->getAsset('bullsquid-transparent.svg'),
                200,
            ],
            [
                $this->templateHelper->getAsset('style.css'),
                200,
            ],
            // [
            //     $this->templateHelper->getResource('202111271344571.jpg'),
            //     200,
            // ],
            [
                $this->router->generateUrl('article', ['guide-sven-co-op']),
                404,
            ],
            [
                $this->router->generateUrl('articles', ['tests']),
                404,
            ],
        ];

        // @todo Does context have a point?
        // $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        stream_context_create(['http' => ['ignore_errors' => true]]);

        foreach ($urls as $url) {
            echo "Fetching $url[0]...\n";
            // @todo Does $home have a point?
            // $home = file_get_contents($url);
            try {
                file_get_contents($url[0]);
            } catch (RuntimeException $e) {
                if (!str_contains($e->getMessage(), $url[1])) {
                    throw $e;
                }
            }
            
            $httpResponseCode = $http_response_header[0];

            $this->tester->assertStringContains(
                $httpResponseCode,
                $url[1],
                "HTTP response code is $httpResponseCode, should contain 200 OK.",
            );
        }

        $keyName0 = new KeyName('Hello !');
        $this->tester->assertEquals('hello', $keyName0->__toString());

        $keyName1 = new KeyName('myArticle');
        $this->tester->assertEquals('my_article', $keyName1->__toString());

        return $this->tester->getErrors();
    }
}