<?php

namespace Hso\TestApi\Test;

use Hso\TestApi\Middleware\SecurityApiManagerMiddleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MiddlewareTest extends TestCase
{
    private $middleware ;
    public function testGetRequest()
    {
        /**
         * @var $middleware SecurityApiManagerMiddleware
         */
        $middleware = $this->middleware;

        // 创建一个简单的请求和响应
        $request = Request::create('/test/api');
        $response = new JsonResponse(['data' => 'test']);

        // 调用中间件的 handle 方法
        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // 验证中间件是否正确处理了响应
        $this->assertInstanceOf(JsonResponse::class, $result);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = $this->app->make(SecurityApiManagerMiddleware::class);
    }
}