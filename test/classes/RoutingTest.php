<?php

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use FastRoute\Dispatcher;
use PhpMyAdmin\Controllers\HomeController;
use PhpMyAdmin\Routing;

use function copy;

use const CACHE_DIR;
use const ROOT_PATH;

/**
 * @covers \PhpMyAdmin\Routing
 */
class RoutingTest extends AbstractTestCase
{
    /**
     * Test for Routing::getDispatcher
     */
    public function testGetDispatcher(): void
    {
        $dispatcher = Routing::getDispatcher();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertSame(
            [Dispatcher::FOUND, HomeController::class, []],
            $dispatcher->dispatch('GET', '/')
        );
    }

    public function testGetDispatcherWithValidCacheFile(): void
    {
        $GLOBALS['cfg']['environment'] = null;

        $this->assertDirectoryIsWritable(CACHE_DIR);
        $this->assertTrue(copy(
            ROOT_PATH . 'test/test_data/routes/routes-valid.cache.txt',
            CACHE_DIR . 'routes.cache.php'
        ));

        $dispatcher = Routing::getDispatcher();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertSame(
            [Dispatcher::FOUND, HomeController::class, []],
            $dispatcher->dispatch('GET', '/')
        );

        $this->assertFileEquals(
            CACHE_DIR . 'routes.cache.php',
            ROOT_PATH . 'test/test_data/routes/routes-valid.cache.txt'
        );
    }

    public function testGetDispatcherWithInvalidCacheFile(): void
    {
        $GLOBALS['cfg']['environment'] = null;

        $this->assertDirectoryIsWritable(CACHE_DIR);
        $this->assertTrue(copy(
            ROOT_PATH . 'test/test_data/routes/routes-invalid.cache.txt',
            CACHE_DIR . 'routes.cache.php'
        ));

        $dispatcher = Routing::getDispatcher();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertSame(
            [Dispatcher::FOUND, HomeController::class, []],
            $dispatcher->dispatch('GET', '/')
        );

        $this->assertFileNotEquals(
            CACHE_DIR . 'routes.cache.php',
            ROOT_PATH . 'test/test_data/routes/routes-invalid.cache.txt'
        );
    }

    /**
     * Test for Routing::getCurrentRoute
     */
    public function testGetCurrentRouteNoParams(): void
    {
        $this->assertSame('/', Routing::getCurrentRoute());
    }

    /**
     * Test for Routing::getCurrentRoute
     */
    public function testGetCurrentRouteGet(): void
    {
        $_GET['route'] = '/test';
        $this->assertSame('/test', Routing::getCurrentRoute());
    }

    /**
     * Test for Routing::getCurrentRoute
     */
    public function testGetCurrentRoutePost(): void
    {
        unset($_GET['route']);
        $_POST['route'] = '/testpost';
        $this->assertSame('/testpost', Routing::getCurrentRoute());
    }

    /**
     * Test for Routing::getCurrentRoute
     */
    public function testGetCurrentRouteGetIsOverPost(): void
    {
        $_GET['route'] = '/testget';
        $_POST['route'] = '/testpost';
        $this->assertSame('/testget', Routing::getCurrentRoute());
    }

    /**
     * Test for Routing::getCurrentRoute
     */
    public function testGetCurrentRouteRedirectDbStructure(): void
    {
        unset($_POST['route']);
        unset($_GET['route']);
        $_GET['db'] = 'testDB';
        $this->assertSame('/database/structure', Routing::getCurrentRoute());
    }

    /**
     * Test for Routing::getCurrentRoute
     */
    public function testGetCurrentRouteRedirectSql(): void
    {
        $_GET['db'] = 'testDB';
        $_GET['table'] = 'tableTest';
        $this->assertSame('/sql', Routing::getCurrentRoute());
    }
}
