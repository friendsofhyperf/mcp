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

use FriendsOfHyperf\MCP\ServerRegistry;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use ModelContextProtocol\SDK\Server\McpServer;
use ModelContextProtocol\SDK\Shared\Transport as ServerTransport;
use RuntimeException;
use Throwable;

use function Hyperf\Support\make;

class RegisterServerListener implements ListenerInterface
{
    public function __construct(
        protected DispatcherFactory $dispatcherFactory, // Don't remove this line
        protected ConfigInterface $config,
        protected ServerRegistry $registry,
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

        foreach ($servers as $options) {
            $name = $options['name'] ?? '';
            $serverInfo = Arr::only($options, ['name', 'version', 'description']);
            $serverOptions = Arr::only($options['options'] ?? [], ['logger', 'enforceStrictCapabilities']);
            $this->registry->register(
                $name,
                $server = new McpServer($serverInfo, $serverOptions)
            );

            if (! isset($options['sse']['server'], $options['sse']['endpoint'])) {
                continue;
            }

            $serverName = $options['sse']['server'];
            $endpoint = $options['sse']['endpoint'];
            $middlewares = $options['sse']['middlewares'] ?? [];
            $transport = make(ServerTransport::class, [
                'endpoint' => $endpoint,
            ]);
            $server->connect($transport);

            $this->registerSseRouter($transport, $serverName, $endpoint, [
                'middlewares' => $middlewares,
            ]);
        }
    }

    protected function registerSseRouter(
        ServerTransport $transport,
        string $serverName,
        string $endpoint,
        array $options = []
    ): void {
        Router::addServer($serverName, function () use ($transport, $endpoint, $options) {
            Router::addRoute(
                ['GET', 'POST'],
                $endpoint,
                function (RequestInterface $request) use ($transport) {
                    try {
                        match ($request->getMethod()) {
                            'GET' => $transport->start(),
                            'POST' => $transport->handleMessage($request->getBody()->getContents()),
                            default => throw new RuntimeException('Method not allowed'),
                        };
                    } catch (Throwable $e) {
                        $transport->handleError($e);
                    }
                },
                $options
            );
        });
    }
}
