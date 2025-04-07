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

use FriendsOfHyperf\MCP\Collector\PromptCollector;
use FriendsOfHyperf\MCP\Collector\ResourceCollector;
use FriendsOfHyperf\MCP\Collector\ToolCollector;
use Hyperf\Contract\ConfigInterface;
use ModelContextProtocol\SDK\Server\McpServer;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ServerRegistry
{
    /**
     * @var array<string, McpServer>
     */
    protected array $servers = [];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
    ) {
    }

    public function register(string $name, McpServer $server): void
    {
        foreach ((array) ToolCollector::get($name, []) as $name => $tool) {
            $server->tool(
                name: $name,
                handler: [$this->container->get($tool['className']), $tool['target']],
                definition: $tool['definition'],
            );
        }

        foreach ((array) ResourceCollector::get($name, []) as $scheme => $resource) {
            $server->resource(
                scheme: $scheme,
                handler: [$this->container->get($resource['className']), $resource['target']],
                template: $resource['template'],
            );
        }

        foreach ((array) PromptCollector::get($name, []) as $prompt) {
            $server->prompt(
                name: $prompt['name'],
                handler: [$this->container->get($prompt['className']), $prompt['target']],
                definition: $prompt['definition'],
            );
        }

        $this->servers[$name] = $server;
    }

    public function get(string $name): McpServer
    {
        if (! isset($this->servers[$name])) {
            throw new RuntimeException(sprintf('Server %s not found.', $name));
        }

        return $this->servers[$name];
    }
}
