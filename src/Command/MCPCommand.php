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

namespace FriendsOfHyperf\MCP\Command;

use FriendsOfHyperf\MCP\ServerRegistry;
use FriendsOfHyperf\MCP\Transport\StdioServerTransport;
use Hyperf\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Throwable;

use function Hyperf\Support\make;

class MCPCommand extends Command
{
    protected bool $coroutine = false;

    protected ?string $signature = 'mcp:run {--name= : The name of the mcp server.}';

    protected string $description = 'This command runs the mcp server.';

    protected QuestionHelper $helper;

    public function __construct(
        protected ServerRegistry $registry,
    ) {
        $this->helper = new QuestionHelper();
        parent::__construct();
    }

    public function handle(): void
    {
        $server = $this->registry->get($this->input->getOption('name'));
        $transport = make(StdioServerTransport::class, [
            'input' => $this->input,
            'output' => $this->output,
        ]);
        $transport->setOnMessage(fn ($message) => $server->handleMessage($message));
        $transport->setOnError(fn ($error) => $server->handleError($error));
        $transport->setOnClose(fn () => $server->handleClose());
        $server->connect($transport);

        while (true) { // @phpstan-ignore-line
            $line = $this->helper->ask($this->input, $this->output, new Question(''));

            if ($line !== false && trim($line) !== '') {
                try {
                    $transport->handleMessage(trim($line));
                } catch (Throwable $e) {
                    $transport->handleError($e);
                }
            }

            usleep(10000); // 10ms
        }
    }
}
