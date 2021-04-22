<?php

namespace App;

use Symfony\Component\HttpFoundation\{Request, Response, BinaryFileResponse};
use Symfony\Component\HttpFoundation\File\Stream;

class ImageController extends BaseController
{
    protected array $sizes;
    protected string $imgPath;
    protected string $cachePath;

    public function __construct()
    {
        $this->sizes = $this->getSizeSettings();
        if (empty($this->sizes)) {
            throw new \RuntimeException('No image size definitions configured.');
        }
        if (!$this->checkDirs()) {
            throw new \RuntimeException('Image directories is not well-configured.');
        }
    }

    public function actionDefault(Request $request)
    {
        try {
            $this->validateQuery($request);
            $target = $this->getDeferred($request->query->get('name'), strtolower($request->query->get('size')));
            $stream = new Stream($target['name']);
            $response = new BinaryFileResponse($stream, Response::HTTP_OK, ['Content-Type' => $target['mime']]);
        }
        catch (\Excepion $e) {
            $response = new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $response->send();
    }

    private function getDeferred(string $name, string $size): array
    {
        $targetName = sprintf('%s/%s_%s.%s', $this->cachePath, $name, $size, $_ENV['IMAGES_EXT'] ?? 'jpg');

        if (!file_exists($targetName)) {
            $mask = sprintf('%s/%s.{%s}', $this->imgPath, basename($name), $_ENV['IMAGES_SUPPORTED'] ?? '*');
            $matches = glob($mask, GLOB_BRACE);
            if (!$matches) {
                throw new \RuntimeException('Source image not found.');
            }
            $sourceName = array_shift($matches);
            if (!$this->resizeImage($sourceName, $targetName, $this->sizes[$size])) {
                throw new \RuntimeException('Cannot defer image.');
            }
        }
        $info = getimagesize($targetName);

        return ['name' => $targetName, 'mime' => $info['mime'], 'sizes' => $info[3]];
    }

    protected function resizeImage(string $source, string $dest, array $size): bool
    {
        if (!$info = getimagesize($source)) {
            throw new \RuntimeException('Unable to get image information.');
        }
        $vertical = $info[1] > $info[0];
        $scaleFactor = $size[(int) $vertical] / $info[(int) $vertical];
        $newSize = [
            (int) ($vertical ? $info[0] * $scaleFactor : $size[0]),
            (int) ($vertical ? $size[1] : $info[1] * $scaleFactor)
        ];
        $mime = explode('/', $info['mime'])[1];
        $call = 'imagecreatefrom' . $mime;
        $targetCall = 'image' . $_ENV['IMAGES_FORMAT'] ?? 'jpeg';

        $src = $call($source);
        if (!is_object($src) || !function_exists($targetCall)) {
            return false;
        }

        $tmp = @imagecreatetruecolor($newSize[0], $newSize[1]);
        if (!is_object($tmp)) {
            return false;
        }
        imagefill($tmp, 0, 0, imagecolorallocate($tmp, 255, 255, 255));
        imagealphablending($tmp, TRUE);
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newSize[0], $newSize[1], $info[0], $info[1]);
        if (!$targetCall($tmp, $dest, (int) $_ENV['JPEG_QUALITY'] ?? 80)) {
            return false;
        }
        imagedestroy($src);
        imagedestroy($tmp);
        gc_collect_cycles();

        return true;
    }

    protected function validateQuery(Request $request)
    {
        if (empty($request->query->get('name'))
            || !isset($this->sizes[$request->query->get('size')])) {
            throw new \RuntimeException('Undefined image size');
        }
    }

    protected function getSizeSettings()
    {
        $sizes = array_filter($_ENV, fn($item) => stripos($item, 'size_') === 0, ARRAY_FILTER_USE_KEY);
        return array_combine(
            array_map(fn($k) => preg_replace('/^size_(.+)$/i', '\1', strtolower($k)), array_keys($sizes)),
            array_map(fn($v) => explode('x', $v), array_values($sizes))
        );
    }

    protected function checkDirs(): bool
    {
        $this->imgPath = realpath(dirname(__DIR__) . '/' . $_ENV['IMAGES_SOURCE']);
        if (!$this->imgPath || !is_dir($this->imgPath)) {
            return false;
        }
        $cachePath = dirname(__DIR__) . '/' . $_ENV['IMAGES_CACHE'];
        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0775, true);
        }
        $this->cachePath = realpath($cachePath);
        if (!$this->cachePath || !is_dir($this->cachePath)) {
            return false;
        }
        return true;
    }
}
