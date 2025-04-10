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

use FriendsOfHyperf\MCP\Contract\ServerTransport;
use FriendsOfHyperf\MCP\ServerRegistry;
use FriendsOfHyperf\MCP\Transport\StdioServerTransport;
use Hyperf\Command\Command;
use Symfony\Component\Console\Exception\MissingInputException;
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
        /** @var ServerTransport $transport */
        $transport = make(StdioServerTransport::class, [
            'input' => $this->input,
            'output' => $this->output,
        ]);
        $server->connect($transport);
        $transport->start();

        while (true) { // @phpstan-ignore-line
            try {
                $line = $this->helper->ask($this->input, $this->output, new Question(''));
                if ($line !== false && trim($line) !== '') {
                    $transport->handleMessage(trim($line));
                }
            } catch (MissingInputException $e) {
                // Ignore the exception when the input is empty
                continue;
            } catch (Throwable $e) {
                $transport->handleError($e);
            }
        }
    }
}
