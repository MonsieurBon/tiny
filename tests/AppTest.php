<?php


namespace Tiny;


use Monolog\Handler\NullHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        LoggerFactory::getLogger('access')->pushHandler(new NullHandler());
        LoggerFactory::getLogger('error')->pushHandler(new NullHandler());
    }

    public function testNotFound() {
        $request = Request::create('/bar');
        $request->overrideGlobals();

        $app = new App();
        $app->get('/foo', function () {
            $this->assertTrue(false, 'This should not be called');
        });
        ob_start();
        $app->run();
        $response = ob_get_clean();

        $this->assertEquals('Not found', $response);
    }

    /**
     * @runInSeparateProcess
     */
    public function testNotAllowed() {
        $request = Request::create('/bar');
        $request->overrideGlobals();

        $app = new App();
        $app->post('/bar', function () {
            $this->assertTrue(false, 'This should not be called');
        });
        ob_start();
        $app->run();
        $headers = xdebug_get_headers();
        $response = ob_get_clean();

        $this->assertContains('Allow: POST', $headers);
        $this->assertEquals('Method not allowed', $response);
    }

    public function testHelloWorld() {
        $expectedResponse = 'Hello Foo';
        $request = Request::create('/hello/Foo');
        $request->overrideGlobals();

        $app = new App();
        $app->get('/hello/{name}', function (Request $request) use ($expectedResponse) {
            $this->assertTrue($request instanceof Request);
            $this->assertEquals('Foo', $request->get('name'));
            return new Response($expectedResponse);
        });
        ob_start();
        $app->run();
        $response = ob_get_clean();

        $this->assertEquals($expectedResponse, $response);
    }

    public function testInternalServerError() {
        $request = Request::create('/foo');
        $request->overrideGlobals();

        $app = new App();
        $app->get('/foo', function () {
            throw new \Exception('foo');
        });
        ob_start();
        $app->run();
        $response = ob_get_clean();

        $this->assertEquals('Internal Server Error', $response);
    }
}