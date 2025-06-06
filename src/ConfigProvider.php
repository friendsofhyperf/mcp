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
use FriendsOfHyperf\MCP\Command\MCPCommand;
use FriendsOfHyperf\MCP\Listener\OnPipeMessageListener;
use FriendsOfHyperf\MCP\Listener\RegisterServerListener;

defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        PromptCollector::class,
                        ResourceCollector::class,
                        ToolCollector::class,
                    ],
                ],
            ],
            'commands' => [
                MCPCommand::class,
            ],
            'dependencies' => [
                Contract\IdGenerator::class => Generator\IdGenerator::class,
                Contract\SessionIdGenerator::class => Generator\SessionIdGenerator::class,
                \ModelContextProtocol\SDK\Shared\Transport::class => Transport\SseServerTransport::class,
            ],
            'listeners' => [
                RegisterServerListener::class,
                OnPipeMessageListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of mcp.',
                    'source' => __DIR__ . '/../publish/mcp.php',
                    'destination' => BASE_PATH . '/config/autoload/mcp.php',
                ],
            ],
        ];
    }
}
