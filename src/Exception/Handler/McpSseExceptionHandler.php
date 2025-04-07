<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Exception\Handler;

use Hyperf\Engine\Http\Stream;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Server\Response as HttpResponse;
use Hyperf\HttpServer\Contract\RequestInterface;
use ModelContextProtocol\SDK\Shared\Transport;
use Psr\Http\Message\ResponseInterface;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class McpSseExceptionHandler extends ExceptionHandler
{
    public function __construct(protected Transport $transport, protected RequestInterface $request)
    {
    }

    public function handle(Throwable $throwable, ResponsePlusInterface $response): ResponseInterface
    {
        $this->transport->send(json_encode([])); // @TODO send error message to client
        return (new HttpResponse())->setStatus(202)->setBody(new Stream('Accepted'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
