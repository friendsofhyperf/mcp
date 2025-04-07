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

use Hyperf\Di\Annotation\AbstractAnnotation;
use ReflectionParameter;

abstract class BaseAnnotation extends AbstractAnnotation
{
    protected static function getDescription(ReflectionParameter $parameter): string
    {
        foreach ($parameter->getAttributes() as $attribute) {
            if ($attribute->getName() === Description::class) {
                return $attribute->newInstance()->description;
            }
        }

        return '';
    }
}
