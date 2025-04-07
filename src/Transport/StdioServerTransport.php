<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
     * @var null|callable callback for when a message is received
     */
    private $onMessage;

    /**
     * @var null|callable callback for when the connection is closed
     */
    private $onClose;

    /**
     * @var null|callable callback for when an error occurs
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
    }

    public function send(string $message): void
    {
        if ($this->active) {
            $this->output->write($message);
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
            ($this->onMessage)($message);
        }
    }

    public function handleError(Throwable $error): void
    {
        if ($this->onError) {
            ($this->onError)($error);
        }
    }

    public function handleClose(): void
    {
        if ($this->onClose) {
            ($this->onClose)();
        }
    }
}
