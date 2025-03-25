<?php

declare(strict_types=1);

namespace KanyJoz\Tests\Controller;

use KanyJoz\AniMerged\Controller\AnimationController;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use KanyJoz\AniMerged\Utils\StatusCode;
use KanyJoz\AniMerged\Utils\UrlBag;
use KanyJoz\Tests\Repository\AnimationStubRepository;
use Monolog\Logger;
use Monolog\Test\TestCase;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;

class AnimationControllerTest extends TestCase
{
    private AnimationController $controller;

    protected function setUp(): void
    {
        $urlBag = new UrlBag();
        $formatter = new ResponseFormatter(new Logger('test'));
        $animations = new AnimationStubRepository();
        $this->controller = new AnimationController(
            $urlBag,
            $formatter,
            $animations
        );
    }

    public function testShow(): void
    {
        $req = new ServerRequest('GET', '/v1/animations/1');
        $res = new Response();
        $args = ['id' => '1'];

        $response = $this->controller->show($req, $res, $args);
        $this->assertSame(StatusCode::OK(), $res->getStatusCode());

        $expectedJson = '{"anime":{"id":0,"title":"DANDADAN","version":0,"year":2024,"season":"Spring","genres":["shonen","supernatural"]}}';

        $response->getBody()->rewind();
        $this->assertSame($expectedJson, $response->getBody()->getContents());
    }

    public function testShowNotFound(): void
    {
        $req = new ServerRequest('GET', '/v1/animations/1');
        $res = new Response();
        $args = ['id' => '4'];

        $response = $this->controller->show($req, $res, $args);
        $this->assertSame(StatusCode::NOT_FOUND(), $response->getStatusCode());
    }
}