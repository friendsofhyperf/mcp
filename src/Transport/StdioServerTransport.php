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

use ModelContextProtocol\SDK\Shared\Transport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StdioServerTransport implements Transport
{
    use Traits\InteractsWithCallbacks;

    /**
     * @var bool whether the transport is active
     */
    private bool $active = false;

    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {
        $this->active = true;
    }

    public function close(): void
    {
        $this->active = false;

        $this->handleClose();
    }

    public function send(string $message): void
    {
        if ($this->active) {
            $this->output->writeln($message);
        }
    }
}
