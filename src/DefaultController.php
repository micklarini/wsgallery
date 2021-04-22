<?php

namespace App;

use Symfony\Component\HttpFoundation\{Request, Response};

class DefaultController extends BaseController implements TemplatedInterface
{
    use TwigTemplateTrait;

    public function actionDefault(Request $request)
    {
        $vars = [
            'page' => [
                'title' => 'WineStyle - test task',
                'description' => 'Test task for WineStyle. Author: Michael Larin.',
            ],
            'images' => $this->getImageSamples(),
        ];

        $response = new Response($this->renderTemplate('index', $vars), Response::HTTP_OK);
        $response->send();
    }

    private function getImageSamples(): ?array
    {
        $mask = sprintf('%s/*.{%s}', dirname(__DIR__) . '/' . $_ENV['IMAGES_SOURCE'], $_ENV['IMAGES_SUPPORTED'] ?? 'jpg');
        $matches = glob($mask, GLOB_BRACE);
        return $matches 
            ? array_map(fn($name) => pathinfo($name, PATHINFO_FILENAME), array_slice($matches, 0, 10)) 
            : null;
    }
}
