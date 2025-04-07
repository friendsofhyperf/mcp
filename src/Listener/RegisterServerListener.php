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
            $server->connect($transport);

            Router::addServer($server['sse']['server'], function () use ($server, $transport) {
                Router::get($route = $server['sse']['route'] ?? '/', function () use ($transport, $route) {
                    return $transport->start($route);
                });
                Router::post($route, function (RequestInterface $request) use ($transport) {
                    return $transport->handleMessage($request->getBody()->getContents());
                });
            });
        }
    }
}
