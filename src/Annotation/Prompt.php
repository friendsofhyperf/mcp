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
use Hyperf\Di\ReflectionManager;
use InvalidArgumentException;

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
            ->get($this->server)
            ->prompt(
                name: $this->name,
                handler: [$this->getContainer()->get($className), $target],
                definition: $this->buildDefinition($className, $target),
            );
    }

    private function buildDefinition(string $className, string $target): array
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $this->name)) {
            throw new InvalidArgumentException('Prompt name must be alphanumeric and underscores.');
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'arguments' => $this->buildArguments($className, $target),
        ];
    }

    private function buildArguments(string $className, string $target): array
    {
        $reflection = ReflectionManager::reflectMethod($className, $target);
        $parameters = $reflection->getParameters();
        $arguments = [];

        foreach ($parameters as $parameter) {
            $arguments[] = [
                'name' => $parameter->getName(),
                'description' => self::getDescription($parameter),
                'required' => ! $parameter->isOptional(),
            ];
        }

        return $arguments;
    }
}
