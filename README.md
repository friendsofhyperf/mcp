# Model Context Protocol for Hyperf

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/mcp/v/stable.svg)](https://packagist.org/packages/friendsofhyperf/mcp)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/mcp/downloads.svg)](https://packagist.org/packages/friendsofhyperf/mcp)
[![License](https://poser.pugx.org/friendsofhyperf/mcp/license.svg)](https://packagist.org/packages/friendsofhyperf/mcp)

`friendsofhyperf/mcp` 是一个基于 [Hyperf](https://hyperf.io) 框架的 Model Context Protocol 服务器实现，帮助您创建和管理 MCP 服务，用于构建智能 AI 助手交互系统。

## 简介

Model Context Protocol (MCP) 是一种用于 AI 模型和应用程序之间交互的协议，`friendsofhyperf/mcp` 为 Hyperf 框架提供了完整的 MCP 服务器实现，支持：

- 工具定义与调用
- 资源管理
- 提示模板
- SSE (Server-Sent Events) 和命令行交互方式

## 安装

通过 Composer 安装：

```bash
composer require friendsofhyperf/mcp
```

## 配置

安装完成后，执行以下命令发布配置文件：

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/mcp
```

配置文件位于 `config/autoload/mcp.php`：

```php
<?php

return [
    'servers' => [
        [
            'name' => 'demo',
            'version' => '1.0.0',
            'description' => 'This is a demo mcp server.',
            // SSE 服务器配置选项
            'sse' => [
                'server' => 'http',
                'endpoint' => '/sse',
            ],
        ],
    ],
];
```

## 快速开始

### 创建工具

使用 `#[Tool]` 注解创建工具：

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\MCP\Annotation\Tool;

class FileController
{
    #[Tool(name: 'read_file', description: '读取文件内容', server: 'demo')]
    public function readFile(string $path): string
    {
        return file_get_contents($path);
    }

    #[Tool(name: 'write_file', description: '写入文件内容', server: 'demo')]
    public function writeFile(string $path, string $content): bool
    {
        return (bool) file_put_contents($path, $content);
    }
}
```

### 创建资源

使用 `#[Resource]` 注解创建资源：

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\MCP\Annotation\Resource;

class FileController
{
    #[Resource(scheme: 'file', server: 'demo')]
    public function getResource(string $path): string
    {
        return file_get_contents($path);
    }
}
```

### 创建 Prompt

使用 `#[Prompt]` 注解创建 Prompt：

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\MCP\Annotation\Prompt;

class ChatController
{
    #[Prompt(name: 'chat', description: '聊天功能', server: 'demo')]
    public function chat(string $message): string
    {
        return "您发送的消息是：{$message}";
    }
}
```

## 运行服务

### 通过 HTTP 服务器运行

MCP 服务会自动注册到 Hyperf 的 HTTP 服务器中。启动 Hyperf HTTP 服务器：

```bash
php bin/hyperf.php start
```

然后可以通过配置的 SSE 端点（如 `/sse`）访问 MCP 服务。

### 通过命令行运行

您也可以在命令行模式下运行 MCP 服务：

```bash
php bin/hyperf.php mcp:run --name=demo
```

## 工具包介绍

MCP 服务器提供以下主要功能：

1. **工具 (Tools)**: 可以通过注解方式定义工具，供 AI 模型调用来执行特定操作。
2. **资源 (Resources)**: 定义资源处理器，用于获取系统资源。
3. **提示 (Prompts)**: 定义提示处理器，用于与 AI 模型交互。

## 架构

核心组件：

- **ServerRegistry**: 服务注册器，管理所有 MCP 服务实例。
- **Collectors**: 收集工具、资源和提示定义，并在运行时注册它们。
- **Transport**: 提供与客户端通信的传输层，包括 SSE 和 STDIO 两种方式。

## VS Code 扩展

项目包含 `.vscode/mcp.json` 配置文件，可用于配置 VS Code MCP 扩展。您可以在此文件中自定义 MCP 服务器配置。

## 开发和测试

### 代码风格修复

```bash
composer cs-fix
```

### 静态分析

```bash
composer analyse
```

### 运行测试

```bash
composer test
```

## 贡献

欢迎提交 Pull Request 或创建 Issue 来完善此项目。

## 协议

本项目采用 MIT 许可证。
