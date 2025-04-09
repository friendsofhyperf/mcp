<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP;

use FriendsOfHyperf\MCP\Annotation\Prompt;
use FriendsOfHyperf\MCP\Annotation\Resource;
use FriendsOfHyperf\MCP\Annotation\Tool;
use FriendsOfHyperf\MCP\Collector\PromptCollector;
use FriendsOfHyperf\MCP\Collector\ResourceCollector;
use FriendsOfHyperf\MCP\Collector\ToolCollector;
use ModelContextProtocol\SDK\Server\McpServer;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function Hyperf\Tappable\tap;

class ServerRegistry
{
    /**
     * @var array<string, McpServer>
     */
    protected array $servers = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(string $name, McpServer $server): void
    {
        $this->servers[$name] = tap($server, function ($server) use ($name) {
            $serverName = $name;

            foreach ((array) ToolCollector::get($serverName, []) as $tool) {
                /** @var Tool $tool */
                $handler = function (array $params) use ($tool) {
                    return call_user_func(
                        [$this->container->get($tool->className), $tool->target],
                        ...$params,
                    );
                };
                $server->tool(
                    name: $tool->name,
                    handler: $handler,
                    definition: $tool->definition,
                );
            }

            foreach ((array) ResourceCollector::get($serverName, []) as $resource) {
                /* @var Resource $resource */
                $server->resource(
                    scheme: $resource->scheme,
                    handler: [$this->container->get($resource->className), $resource->target],
                    template: $resource->template,
                );
            }

            foreach ((array) PromptCollector::get($serverName, []) as $prompt) {
                /** @var Prompt $prompt */
                $handler = function (array $arguments) use ($prompt) {
                    return call_user_func(
                        [$this->container->get($prompt->className), $prompt->target],
                        ...$arguments,
                    );
                };
                $server->prompt(
                    name: $prompt->name,
                    handler: $handler,
                    definition: $prompt->definition,
                );
            }
        });
    }

    public function get(string $name): McpServer
    {
        if (! isset($this->servers[$name])) {
            throw new RuntimeException(sprintf('Server %s not found.', $name));
        }

        return $this->servers[$name];
    }
}
