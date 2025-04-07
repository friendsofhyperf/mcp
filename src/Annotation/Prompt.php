<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */

namespace FriendsOfHyperf\MCP\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Prompt extends BaseAnnotation
{
    public function __construct(
        public string $name = '',
        public string $description = '',
        public string $server = '',
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $this->getServerManager()
            ->getServer($this->server)
            ->prompt(
                name: $this->name,
                handler: [$this->getContainer()->get($className), $target],
                definition: [
                    'description' => $this->description,
                    'arguments' => [], // @TODO build arguments
                ],
            );
    }
}
