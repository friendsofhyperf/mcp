<?php

declare(strict_types=1);
/**
 * This file is part of huangdijia/mcp-php-sdk.
 *
 * @link     https://github.com/huangdijia/mcp-php-sdk
 * @document https://github.com/huangdijia/mcp-php-sdk/blob/main/README.md
 * @contact  Deeka Wong <huangdijia@gmail.com>
 */
return [
    'servers' => [
        [
            'name' => 'demo',
            'version' => '1.0.0',
            'description' => 'This is a demo mcp server.',
            // The options of the sse server
            'sse' => [
                'server' => 'http',
                'endpoint' => '/sse',
            ],
        ],
    ],
];
