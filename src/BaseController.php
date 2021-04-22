<?php

namespace App;

use Symfony\Component\HttpFoundation\{Request, Response};

class BaseController
{
    public function __construct()
    {
        $traits = \class_uses_deep($this, true);
        array_walk($traits, function($trait) {
            $stack = explode('\\', $trait);
            $call = end($stack) . 'Init';
            method_exists($this, $call) ? $this->$call() : null;
        });
    }
}
