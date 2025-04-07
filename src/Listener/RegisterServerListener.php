<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Listener;

use FriendsOfHyperf\MCP\ServerManager;
use FriendsOfHyperf\MCP\Transport\SseServerTransport;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use RuntimeException;
use Throwable;

use function Hyperf\Support\make;

class RegisterServerListener implements ListenerInterface
{
    public function __construct(
        protected DispatcherFactory $dispatcherFactory, // Don't remove this line
        protected ConfigInterface $config,
        protected ServerManager $serverManager,
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $servers = $this->config->get('mcp.servers', []);

        foreach ($servers as $name => $server) {
            $server = $this->serverManager->getServer($name);
            $transport = make(SseServerTransport::class);
            $transport->setOnMessage(fn ($message) => $server->handleMessage($message));
            $transport->setOnError(fn ($error) => $server->handleError($error));
            $transport->setOnClose(fn () => $server->handleClose());
            $server->connect($transport);

            Router::addServer($server['sse']['server'] ?? 'http', function () use ($server, $transport) {
                Router::addRoute(
                    ['GET', 'POST'],
                    $route = $server['sse']['route'] ?? '/',
                    function (RequestInterface $request) use ($transport, $route) {
                        try {
                            match ($request->getMethod()) {
                                'GET' => $transport->start($route),
                                'POST' => $transport->handleMessage($request->getBody()->getContents()),
                                default => throw new RuntimeException('Method not allowed'),
                            };
                        } catch (Throwable $e) {
                            $transport->handleError($e);
                        }
                    }
                );
            });
        }
    }
}
