<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Transport;

use FriendsOfHyperf\MCP\ConnectionManager;
use FriendsOfHyperf\MCP\Contract\IdGenerator;
use FriendsOfHyperf\MCP\Contract\SessionIdGenerator;
use FriendsOfHyperf\MCP\SsePipeMessage;
use Hyperf\Engine\Contract\Http\Writable;
use Hyperf\Engine\Http\EventStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use ModelContextProtocol\SDK\Server\Transport\AbstractTransport;
use ModelContextProtocol\SDK\Types;
use Psr\Container\ContainerInterface;
use Swoole\Server;
use Throwable;

use function Hyperf\Coroutine\wait;
use function Hyperf\Support\msleep;

class SseServerTransport extends AbstractTransport
{
    /**
     * @var Server|null
     */
    protected $server;

    protected RequestInterface $request;

    protected ResponseInterface $response;

    protected IdGenerator $idGenerator;

    protected SessionIdGenerator $sessionIdGenerator;

    protected ConnectionManager $connections;

    public function __construct(
        protected ContainerInterface $container,
        protected string $endpoint = '/sse',
    ) {
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
        $this->idGenerator = $container->get(IdGenerator::class);
        $this->sessionIdGenerator = $container->get(SessionIdGenerator::class);
        $this->connections = $container->get(ConnectionManager::class);
    }

    public function start(): void
    {
        $sessionId = $this->sessionIdGenerator->generate();
        /** @var Writable $psr7Response */
        $psr7Response = $this->response->getConnection(); // @phpstan-ignore method.notFound
        $connection = (new EventStream($psr7Response))
            ->write('event: endpoint' . PHP_EOL)
            ->write("data: {$this->endpoint}?sessionId={$sessionId}" . PHP_EOL . PHP_EOL);
        $this->connections->register($sessionId, $connection);

        defer(function () use ($sessionId) {
            $this->connections->unregister($sessionId);
            $this->close();
        });

        wait(
            closure: function () use ($psr7Response) {
                try {
                    while (true) {
                        $ping = json_encode([
                            'jsonrpc' => Types::JSONRPC_VERSION,
                            'id' => $this->idGenerator->generate(),
                            'method' => 'ping',
                        ]);
                        if (! $psr7Response->write($ping)) { // The connection of client is closed
                            break;
                        }
                        msleep(1000);
                    }
                } catch (Throwable $e) {
                }
            },
            timeout: -1
        );
    }

    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if ($this->connections->has($sessionId)) {
            $this->connections->get($sessionId)->write("event: message\ndata: {$message}\n\n");
            return;
        }

        $this->server ??= $this->container->get(Server::class);
        $workerCount = $this->server->setting['worker_num'] - 1;
        $pipeMessage = new SsePipeMessage($sessionId, $message);

        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId === $this->server->worker_id) {
                continue;
            }

            $this->server->sendMessage($pipeMessage, $workerId);
        }
    }
}
