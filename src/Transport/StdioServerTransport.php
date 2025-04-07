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
use Throwable;

class StdioServerTransport implements Transport
{
    /**
     * @var bool whether the transport is active
     */
    private bool $active = false;

    /**
     * @var callable|null callback for when a message is received
     */
    private $onMessage;

    /**
     * @var callable|null callback for when the connection is closed
     */
    private $onClose;

    /**
     * @var callable|null callback for when an error occurs
     */
    private $onError;

    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {
        $this->active = true;
    }

    public function close(): void
    {
        $this->active = false;

        if ($this->onClose) {
            call_user_func($this->onClose);
        }
    }

    public function send(string $message): void
    {
        if ($this->active) {
            $this->output->writeln($message);
        }
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
}
