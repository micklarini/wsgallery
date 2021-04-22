<?php

namespace App;

interface TemplatedInterface
{
    public function renderTemplate(string $name, array $vars): string;
}
