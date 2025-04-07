<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Command;

use FriendsOfHyperf\MCP\ServerManager;
use FriendsOfHyperf\MCP\Transport\StdioServerTransport;

class MCPCommand extends \Hyperf\Command\Command
{
    protected ?string $signature = 'mcp:run {--name= : The name of the mcp server.}';

    protected string $description = 'This command runs the mcp server.';

    public function __construct(
        protected ServerManager $serverManager,
        protected StdioServerTransport $transport
    ) {
    }

    public function handle(): void
    {
        $server = $this->serverManager->getServer($this->input->getOption('name'));
        $server->connect($this->transport);

        $input = STDIN;
        stream_set_blocking($input, false);

        while (true) {
            $line = fgets($input);

            if ($line !== false && trim($line) !== '') {
                $this->transport->handleMessage(trim($line));
            }

            usleep(10000); // 10ms
        }
    }
}
