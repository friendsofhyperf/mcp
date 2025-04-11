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

use FriendsOfHyperf\MCP\Contract\IdGenerator;
use FriendsOfHyperf\MCP\Contract\SessionIdGenerator;
use Hyperf\Coroutine\WaitGroup;
use Hyperf\Engine\Contract\Http\Writable;
use Hyperf\Engine\Http\EventStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use ModelContextProtocol\SDK\Server\Transport\AbstractTransport;
use ModelContextProtocol\SDK\Types;
use Throwable;

use function Hyperf\Coroutine\co;
use function Hyperf\Support\msleep;

class CoroutineSseServerTransport extends AbstractTransport
{
    /**
     * @var array<int, EventStream>
     */
    protected array $connections = [];

    public function __construct(
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected IdGenerator $idGenerator,
        protected SessionIdGenerator $sessionIdGenerator,
        protected string $endpoint = '/sse',
    ) {
    }

    public function start(): void
    {
        $sessionId = $this->sessionIdGenerator->generate();
        /** @var Writable $psr7Response */
        $psr7Response = $this->response->getConnection(); // @phpstan-ignore method.notFound
        $eventStream = (new EventStream($psr7Response))
            ->write('event: endpoint' . PHP_EOL)
            ->write("data: {$this->endpoint}?sessionId={$sessionId}" . PHP_EOL . PHP_EOL);
        $this->connections[$sessionId] = $eventStream;

        $waitGroup = new WaitGroup();

        co(function () use ($psr7Response, $waitGroup) {
            $waitGroup->add(1);
            try {
                while (true) {
                    $ping = json_encode([
                        'jsonrpc' => Types::JSONRPC_VERSION,
                        'id' => $this->idGenerator->generate(),
                        'method' => 'ping',
                    ]);
                    if (! $psr7Response->write($ping)) {
                        break;
                    }
                    msleep(1000);
                }
            } catch (Throwable $e) {
            } finally {
                $waitGroup->done();
            }
        });

        $waitGroup->wait();

        if (isset($this->connections[$sessionId])) {
            unset($this->connections[$sessionId]);
        }

        // $this->close();
    }

    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if (! isset($this->connections[$sessionId])) {
            return;
        }

        $this->connections[$sessionId]->write("event: message\ndata: {$message}\n\n");
    }

    public function close(): void
    {
        $this->handleClose();
    }
}
