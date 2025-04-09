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
use FriendsOfHyperf\MCP\Collector\ToolCollector;
use Hyperf\Di\ReflectionManager;
use InvalidArgumentException;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_METHOD)]
class Tool extends BaseAnnotation
{
    public function __construct(
        public string $name = '',
        public string $description = '',
        public string $server = '',
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $this->className = $className;
        $this->target = $target;

        ToolCollector::set($this->server . '.' . $this->name, $this);
    }

    public function toDefinition(): array
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $this->name)) {
            throw new InvalidArgumentException('Tool name must be alphanumeric and underscores.');
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'inputSchema' => $this->toInputSchema($this->className, $this->target),
        ];
    }

    private function toInputSchema(string $className, string $target): array
    {
        $reflection = ReflectionManager::reflectMethod($className, $target);
        $parameters = $reflection->getParameters();
        $properties = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType()?->getName() ?? 'string'; // @phpstan-ignore method.notFound
            $type = match ($type) {
                'int' => 'integer',
                'float' => 'number',
                'bool' => 'boolean',
                default => $type,
            };
            $properties[$parameter->getName()] = [
                'type' => $type,
                'description' => self::getDescription($parameter),
            ];
        }

        $required = array_filter(
            array_map(fn (ReflectionParameter $parameter) => $parameter->isOptional() ? null : $parameter->getName(), $parameters)
        );

        return array_filter([
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
            '$schema' => 'http://json-schema.org/draft-07/schema#',
        ]);
    }
}
