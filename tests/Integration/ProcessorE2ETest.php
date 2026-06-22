<?php
declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Integration;

use Symfony\Component\HttpFoundation\Request;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\TestController;
use ZJKiza\ResponseProcessor\Tests\Resources\KernelTestCase;

final class ProcessorE2ETest extends KernelTestCase
{
    private array $savedHandlers = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->savedHandlers['error'] = \set_error_handler(static fn () => null);
        \restore_error_handler();

        $this->savedHandlers['exception'] = \set_exception_handler(static fn () => null);
        \restore_exception_handler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        while (\set_error_handler(static fn () => null) !== $this->savedHandlers['error']) {
            \restore_error_handler();
            \restore_error_handler();
        }

        \restore_error_handler();

        while (\set_exception_handler(static fn () => null) !== $this->savedHandlers['exception']) {
            \restore_exception_handler();
            \restore_exception_handler();
        }

        \restore_exception_handler();
    }

    public function testContainerHasProcessorListener(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);

        $container = self::getContainer();

        $this->assertTrue($container->has('ZJKiza\ResponseProcessor\Listener\ProcessorListener'));
        $this->assertTrue($container->has('test.tag_handler'));
        $this->assertTrue($container->has('test.presenter_handler'));
        $this->assertTrue($container->has('test.json_response_listener'));
    }

    public function testListenerInjectsTagData(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        $request = Request::create('/test-listener', 'GET');
        $request->attributes->set('_controller', TestController::class.'::singleAction');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        self::ensureKernelShutdown();

        $this->assertSame(200, $response->getStatusCode());

        $data = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(1, $data);
        $this->assertSame('a', $data[0]['id']);
        $this->assertSame('tag_a', $data[0]['tag']['id']);
        $this->assertNull($data[0]['presenters']);
    }

    public function testListenerInjectsAllDataForMultipleHandlers(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        $request = Request::create('/test-all', 'GET');
        $request->attributes->set('_controller', TestController::class.'::testAction');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        self::ensureKernelShutdown();

        $this->assertSame(200, $response->getStatusCode());

        $data = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(3, $data);

        $this->assertSame('a', $data[0]['id']);
        $this->assertSame('tag_a', $data[0]['tag']['id']);
        $this->assertCount(2, $data[0]['presenters']);
        $this->assertSame('presenter_a', $data[0]['presenters'][0]['id']);

        $this->assertSame('b', $data[1]['id']);
        $this->assertSame('tag_b', $data[1]['tag']['id']);
        $this->assertCount(2, $data[1]['presenters']);
        $this->assertSame('presenter_b', $data[1]['presenters'][0]['id']);

        $this->assertSame('c', $data[2]['id']);
        $this->assertSame('tag_c', $data[2]['tag']['id']);
        $this->assertCount(3, $data[2]['presenters']);
        $this->assertSame('presenter_c1', $data[2]['presenters'][0]['id']);
    }

    public function testListenerDoesNotInjectWithoutAttribute(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        $request = Request::create('/test-no-attribute', 'GET');
        $request->attributes->set('_controller', TestController::class.'::noAttributeAction');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        self::ensureKernelShutdown();

        $this->assertSame(200, $response->getStatusCode());

        $data = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(1, $data);
        $this->assertSame('a', $data[0]['id']);
        $this->assertNull($data[0]['tag']);
        $this->assertNull($data[0]['presenters']);
    }
}
