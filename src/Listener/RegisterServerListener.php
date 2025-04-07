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

use FriendsOfHyperf\MCP\Contract\SseServerTransport;
use FriendsOfHyperf\MCP\ServerRegistry;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use ModelContextProtocol\SDK\Server\McpServer;
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
            $this->registry->register($name, $server = new McpServer($serverInfo));

            $transport = make(SseServerTransport::class);
            $transport->setOnMessage(fn ($message) => $server->handleMessage($message));
            $transport->setOnError(fn ($error) => $server->handleError($error));
            $transport->setOnClose(fn () => $server->handleClose());
            $server->connect($transport);

            $serverName = $options['sse']['server'] ?? 'http';
            $endpoint = $options['sse']['endpoint'] ?? '/sse';

            $this->registerSseRouter($transport, $serverName, $endpoint);
        }
    }

    protected function registerSseRouter(
        SseServerTransport $transport,
        string $serverName,
        string $endpoint
    ): void {
        Router::addServer($serverName, function () use ($transport, $endpoint) {
            Router::addRoute(
                ['GET', 'POST'],
                $endpoint,
                function (RequestInterface $request) use ($transport, $endpoint) {
                    try {
                        match ($request->getMethod()) {
                            'GET' => $transport->start($endpoint),
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
