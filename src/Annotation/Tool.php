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
        $this->getServerManager()
            ->getServer($this->server)
            ->tool(
                name: $this->name,
                handler: [$this->getContainer()->get($className), $target],
                definition: $this->buildDefinition($className, $target),
            );
    }

    /**
     * @return array{name:string,description?:string,inputSchema:array{type:string,properties:array{name:string,type:string,description?:string}[],required:string[],additionalProperties:bool,$schema:string}}
     */
    private function buildDefinition(string $className, string $target): array
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $this->name)) {
            throw new InvalidArgumentException('Tool name must be alphanumeric and underscores.');
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'inputSchema' => $this->buildInputSchema($className, $target),
        ];
    }

    /**
     * @return array{type:string,properties:array{name:string,type:string,description?:string}[],required:string[],additionalProperties:bool,$schema:string}
     */
    private function buildInputSchema(string $className, string $target): array
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
