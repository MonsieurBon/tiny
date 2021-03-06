<?php


namespace Tiny;


use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppTest extends TestCase
{
    private $postHandlerCalled = false;
    /** @var TestHandler */
    private $errorLogHandler = null;

    public function setUp()
    {
        $this->errorLogHandler = new TestHandler();
        LoggerFactory::getLogger('access')->pushHandler(new TestHandler());
        LoggerFactory::getLogger('error')->pushHandler($this->errorLogHandler);
    }

    public function testNotFound()
    {
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
    public function testNotAllowed()
    {
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

    public function testHelloWorld()
    {
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

    public function testInternalServerError()
    {
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

    public function testPostHandler()
    {
        $request = Request::create('/foo');
        $request->overrideGlobals();

        $app = new App();
        $app->get('/foo', function () {
            $postHandler = function () {
                $this->assertFalse($this->postHandlerCalled);
                $this->postHandlerCalled = true;
            };

            return [new Response(), $postHandler];
        });

        ob_start();
        $app->run();
        ob_get_clean();

        $this->assertTrue($this->postHandlerCalled);
    }

    public function testExceptionInPostHandlerIsSilentlyLogged()
    {
        $request = Request::create('/foo');
        $request->overrideGlobals();

        $app = new App();
        $app->get('/foo', function () {
            $postHandler = function () {
                throw new \Exception('foobar');
            };

            return [new Response(), $postHandler];
        });

        ob_start();
        $app->run();
        ob_get_clean();

        $errorLogs = $this->errorLogHandler->getErrorLogs();

        $this->assertEquals(1, count($errorLogs));
        $this->assertEquals('Exception in post handler: foobar', $errorLogs[0]);
    }
}