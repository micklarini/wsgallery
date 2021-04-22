<?php

namespace App;

trait TwigTemplateTrait
{
    private \Twig\Environment $twig;

    public function TwigTemplateTraitInit()
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/' . $_ENV['TEMPLATES_PATH'] ?? '/templates');
        $options = [
            'cache' => $_ENV['TEMPLATES_CACHE'] ? dirname(__DIR__) . '/' . $_ENV['TEMPLATES_CACHE'] : false, 
            'debug' => $_ENV['TEMPLATES_DEBUG'] ?? false
        ];
        $this->twig = new \Twig\Environment($loader, $options);
    }
    
    public function renderTemplate(string $name, array $vars): string
    {
        return $this->twig->render("$name.html.twig", $vars);
    }
}
