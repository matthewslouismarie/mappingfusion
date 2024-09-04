<?php

namespace MF\Test;

use LM\WebFramework\DataStructures\KeyName;
use MF\Router;
use MF\Twig\TemplateHelper;

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
            $this->router->generateUrl(''),
            $this->router->generateUrl('article', ['nouvel-version-tcm']),
            $this->router->generateUrl('article', ['article-with-thumbnail']),
            $this->templateHelper->getAsset('bullsquid-transparent.svg'),
            $this->templateHelper->getAsset('style.css'),
            $this->templateHelper->getResource('202111271344571.jpg'),
        ];

        // @todo Does context have a point?
        // $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        stream_context_create(['http' => ['ignore_errors' => true]]);

        foreach ($urls as $url) {
            echo "Fetching $url...\n";
            // @todo Does $home have a point?
            // $home = file_get_contents($url);
            file_get_contents($url);
            
            $httpResponseCode = $http_response_header[0];

            $this->tester->assertStringContains(
                $httpResponseCode,
                '200',
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