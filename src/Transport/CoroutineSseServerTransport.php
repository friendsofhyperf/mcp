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
use Hyperf\Coroutine\WaitGroup;
use Hyperf\Engine\Contract\Http\Writable;
use Hyperf\Engine\Http\EventStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use ModelContextProtocol\SDK\Server\Transport\AbstractTransport;
use ModelContextProtocol\SDK\Types;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Coroutine\co;
use function Hyperf\Support\msleep;

class CoroutineSseServerTransport extends AbstractTransport
{
    protected RequestInterface $request;

    protected ResponseInterface $response;

    protected IdGenerator $idGenerator;

    protected SessionIdGenerator $sessionIdGenerator;

    protected ConnectionManager $connectionManager;

    public function __construct(
        protected ContainerInterface $container,
        protected string $endpoint = '/sse',
    ) {
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
        $this->idGenerator = $container->get(IdGenerator::class);
        $this->sessionIdGenerator = $container->get(SessionIdGenerator::class);
        $this->connectionManager = $container->get(ConnectionManager::class);
    }

    public function start(): void
    {
        $sessionId = $this->sessionIdGenerator->generate();
        /** @var Writable $psr7Response */
        $psr7Response = $this->response->getConnection(); // @phpstan-ignore method.notFound
        $eventStream = (new EventStream($psr7Response))
            ->write('event: endpoint' . PHP_EOL)
            ->write("data: {$this->endpoint}?sessionId={$sessionId}" . PHP_EOL . PHP_EOL);
        $this->connectionManager->register($sessionId, $eventStream);

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

        $this->connectionManager->unregister($sessionId);

        // $this->close();
    }

    public function writeMessage(string $message): void
    {
        $sessionId = (string) $this->request->input('sessionId');

        if ($this->connectionManager->has($sessionId)) {
            $this->connectionManager->get($sessionId)->write("event: message\ndata: {$message}\n\n");
        }
    }

    public function close(): void
    {
        parent::close();
    }
}
