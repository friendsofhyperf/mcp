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

use FriendsOfHyperf\MCP\Contract\SseServerTransport;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\EventStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Throwable;

use function Hyperf\Coroutine\co;
use function Hyperf\Support\msleep;

class SseCoroutineServerTransport implements SseServerTransport
{
    /**
     * @var callable|null
     */
    private $onClose;

    /**
     * @var callable|null
     */
    private $onMessage;

    /**
     * @var callable|null
     */
    private $onError;

    /**
     * @var array<int, EventStream>
     */
    private array $connections = [];

    public function __construct(
        protected RequestInterface $request,
        protected ResponseInterface $response,
    ) {
    }

    public function start(string $endpoint): void
    {
        $sessionId = uniqid('sess_', true);
        $psr7Response = $this->response->getConnection(); // @phpstan-ignore method.notFound
        $eventStream = (new EventStream($psr7Response))
            ->write('event: endpoint' . PHP_EOL)
            ->write("data: {$endpoint}?sessionId={$sessionId}" . PHP_EOL . PHP_EOL);
        $this->connections[$sessionId] = $eventStream;

        co(function () use ($sessionId, $psr7Response) {
            while ($psr7Response->write('{}')) {
                msleep(1000);
            }

            CoordinatorManager::until("mcp-sse:sessions:{$sessionId}")->resume();

            $this->handleClose();
        });

        CoordinatorManager::until("mcp-sse:sessions:{$sessionId}")->yield();

        if (isset($this->connections[$sessionId])) {
            unset($this->connections[$sessionId]);
        }
    }

    public function handleMessage(string $message): void
    {
        if ($this->onMessage) {
            call_user_func($this->onMessage, $message);
        }
    }

    public function handleError(Throwable $error): void
    {
        if ($this->onError) {
            call_user_func($this->onError, $error);
        }
    }

    public function handleClose(): void
    {
        if ($this->onClose) {
            call_user_func($this->onClose);
        }
    }

    public function send(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if (! isset($this->connections[$sessionId])) {
            return;
        }

        $this->connections[$sessionId]->write("event: message\ndata: {$message}\n\n");
    }

    public function close(): void
    {
    }

    public function setOnMessage(callable $callback): void
    {
        $this->onMessage = $callback;
    }

    public function setOnClose(callable $callback): void
    {
        $this->onClose = $callback;
    }

    public function setOnError(callable $callback): void
    {
        $this->onError = $callback;
    }
}
