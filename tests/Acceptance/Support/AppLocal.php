<?php
declare(strict_types=1);

namespace Tests\Acceptance\Support;

use Nekudo\ShinyBlog\ShinyBlog;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

class AppLocal implements App
{
    public function visitUrl(string $url): ResponseInterface
    {
        $_SERVER['REQUEST_URI'] = $url;

        $this->getBlog()->run();

        $shinyResponse = $this->getBlog()->run();

        return new Response(
            $shinyResponse->getStatusCode(),
            [],
            $shinyResponse->getBody()
        );
    }

    private static $blog;

    private function getBlog(): ShinyBlog
    {
        if (is_null(self::$blog)) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['HTTP_HOST'] = 'localhost';

            $config = require __DIR__ . '/../../../ShinyBlog/src/config.php';
            self::$blog = new ShinyBlog($config);
        }

        return self::$blog;
    }
}
