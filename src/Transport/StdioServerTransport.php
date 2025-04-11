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

use ModelContextProtocol\SDK\Server\Transport\AbstractTransport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StdioServerTransport extends AbstractTransport
{
    /**
     * @var bool whether the transport is active
     */
    private bool $active = false;

    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {
    }

    public function __destruct()
    {
        $this->close();
    }

    public function start(): void
    {
        $this->active = true;
    }

    public function close(): void
    {
        $this->active = false;

        $this->handleClose();
    }

    public function writeMessage(string $message): void
    {
        if ($this->active) {
            $this->output->writeln($message);
        }
    }
}
