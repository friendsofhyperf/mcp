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
use FriendsOfHyperf\MCP\Collector\ResourceCollector;
use ModelContextProtocol\SDK\Shared\ResourceTemplate;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Resource extends BaseAnnotation
{
    public ?ResourceTemplate $template = null;

    public function __construct(
        public string $scheme = '',
        public string $uri = '',
        public string $name = '',
        public string $description = '',
        public string $mimeType = 'text/plain',
        public string $server = '',
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $this->className = $className;
        $this->target = $target;
        $this->template = $this->toTemplate();

        ResourceCollector::set($this->server . '.' . $this->scheme, $this);
    }

    private function toTemplate(): ResourceTemplate
    {
        return new ResourceTemplate(
            template: $this->uri,
            options: [
                'name' => $this->name,
                'description' => $this->description,
                'mimeType' => $this->mimeType,
            ],
        );
    }
}
