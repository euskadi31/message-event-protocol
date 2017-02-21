<?php

use Hoa\Console;
use Hoa\Dispatcher;
use Hoa\Exception;
use Hoa\Router;

/**
 * @copyright  Copyright © 2007-2016 Hoa community
 */
if (!defined('HOA')) {
    $composer = [
        dirname(__DIR__) . DIRECTORY_SEPARATOR .
        'vendor' . DIRECTORY_SEPARATOR .
        'autoload.php',
        dirname(__DIR__) . DIRECTORY_SEPARATOR .
        '..' . DIRECTORY_SEPARATOR .
        '..' . DIRECTORY_SEPARATOR .
        'autoload.php',
        dirname(__DIR__) . DIRECTORY_SEPARATOR .
        '..' . DIRECTORY_SEPARATOR .
        '..' . DIRECTORY_SEPARATOR .
        '..' . DIRECTORY_SEPARATOR .
        'autoload.php'
    ];

    foreach ($composer as $path) {
        if (file_exists($path)) {
            require_once $path;

            break;
        }
    }

    if (!defined('HOA')) {
        require_once
            dirname(__DIR__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'Consistency' . DIRECTORY_SEPARATOR .
            'Prelude.php';

        require_once
            dirname(__DIR__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'Protocol' . DIRECTORY_SEPARATOR .
            'Wrapper.php';
    }
}

Exception\Error::enableErrorHandler();
Exception::enableUncaughtHandler();

/**
 * Here we go!
 */
try {
    $router = new Router\Cli();
    $router->get(
        'g',
        '(?<command>\w+)?(?<_tail>.*?)',
        'main',
        'main',
        [
            'command' => 'build'
        ]
    );

    $dispatcher = new Dispatcher\ClassMethod([
        'synchronous.call'
            => 'Euskadi31\MessageEventProtocol\Console\(:%variables.command:lU:)',
        'synchronous.able'
            => 'main'
    ]);
    $dispatcher->setKitName('Hoa\Console\Dispatcher\Kit');
    exit((int) $dispatcher->dispatch($router));
} catch (Exception $e) {
    $message = $e->raise(true);
    $code    = 1;
} catch (\Exception $e) {
    $message = $e->getMessage();
    $code    = 2;
}

ob_start();

Console\Cursor::colorize('foreground(white) background(red)');
echo $message, "\n";
Console\Cursor::colorize('normal');
$content = ob_get_contents();

ob_end_clean();

file_put_contents('php://stderr', $content);
exit($code);
