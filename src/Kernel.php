<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

class Kernel {
    
    public Request $request;

    public function __construct()
    {
        try {
            (new Dotenv())->loadEnv(dirname(__DIR__) . '/.env');
            $this->request = Request::createFromGlobals();
        }
        catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function bootstrap()
    {
        try {
            // Рутинга в приниципе никакого в задаче нет, 
            // поэтому ориенитруемся только на имя скрипта.
            $path = $this->request->getBaseUrl() == '' ? $this->request->getPathInfo() : $this->request->getBaseUrl();
            switch (basename($path)) {
                case 'generator.php':
                    $controlName = '\App\ImageController';
                    break;
                case 'demo.php':
                default:
                    $controlName = '\App\DefaultController';
            }
            // Экшнов тоже не предвидится, просто запускаем
            $action = 'actionDefault';
            $controller = new $controlName();
            $controller->$action($this->request);
        }
        catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
}
