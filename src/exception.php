<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统错误 | <?php echo get_class($exception); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .code-block::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        .code-block::-webkit-scrollbar-track {
            background: #111827;
        }
        .code-block::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
        .code-block::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }
        .code-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.2s ease-out;
            opacity: 0;
        }
        .code-panel.expanded {
            max-height: 500px;
            opacity: 1;
        }
        .stack-item {
            transition: background-color 0.15s;
        }
        .line-highlight {
            background-color: rgba(220, 38, 38, 0.15) !important;
            border-left: 3px solid #ef4444;
            margin-left: -12px;
            padding-left: calc(1rem + 9px) !important;
        }
        .line-number {
            user-select: none;
            text-align: right;
        }
        .stack-arrow {
            transition: transform 0.2s ease-out;
        }
        .stack-arrow.rotated {
            transform: rotate(180deg);
        }
        .user-code-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php
/**
 * 渲染代码片段
 */
function renderCodeSnippet(?string $filePath, ?int $targetLine, int $context = 5): string {
    if (empty($filePath) || !file_exists($filePath) || !is_readable($filePath)) {
        return '<div class="p-4 text-gray-400 text-sm">无法读取文件</div>';
    }

    $file = @file($filePath);
    if ($file === false) {
        return '<div class="p-4 text-gray-400 text-sm">无法读取文件内容</div>';
    }

    $totalLines = count($file);
    $targetLine = max(1, $targetLine);
    $start = max($targetLine - $context - 1, 0);
    $end = min($targetLine + $context, $totalLines);

    $html = '<div class="code-block overflow-x-auto bg-gray-900 p-4">';
    $html .= '<pre class="text-sm font-mono leading-relaxed">';

    for ($i = $start; $i < $end; $i++) {
        $currentLine = $i + 1;
        $isTarget = $currentLine === $targetLine;
        $class = $isTarget ? 'line-highlight' : '';
        $textClass = $isTarget ? 'text-white font-medium' : 'text-gray-300';

        $html .= sprintf(
            '<div class="%s px-4 py-0.5 flex items-center">',
            $class
        );
        $html .= sprintf(
            '<span class="line-number inline-block w-10 text-gray-500 flex-shrink-0 mr-4">%d</span>',
            $currentLine
        );
        $html .= sprintf(
            '<span class="%s whitespace-pre">%s</span>',
            $textClass,
            htmlspecialchars(rtrim($file[$i], "\r\n"), ENT_QUOTES, 'UTF-8')
        );
        $html .= '</div>';
    }

    $html .= '</pre></div>';
    return $html;
}

/**
 * 判断是否为应用代码（非 vendor / 框架核心）
 */
function isUserCode(string $filePath): bool {
    $excludePatterns = ['vendor', 'composer', 'framework/src'];
    $normalized = str_replace('\\', '/', strtolower($filePath));
    foreach ($excludePatterns as $pattern) {
        if (strpos($normalized, $pattern) !== false) {
            return false;
        }
    }
    return true;
}
?>

<div class="lg:container mx-auto p-4 lg:p-8 max-w-6xl">
    <!-- 错误概览卡片 -->
    <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden mb-8">
        <div class="border-b border-red-100 bg-red-50/50 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900">
                        <?php echo get_class($exception); ?>
                    </h1>
                </div>
                <?php if ($exception->getCode()): ?>
                    <span class="px-3 py-1 text-sm font-medium text-red-700 bg-red-100 rounded-full">
                        错误码: <?php echo $exception->getCode(); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6">
            <div class="text-gray-800 text-lg font-medium mb-5 leading-relaxed">
                <?php echo htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="mb-2">
                            <span class="text-gray-500 font-medium block mb-0.5">文件位置</span>
                            <span class="text-gray-900 font-mono break-all" title="<?php echo $exception->getFile(); ?>">
                                <?php echo $exception->getFile(); ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 font-medium block mb-0.5">错误行号</span>
                            <span class="text-red-600 font-mono font-bold text-lg"><?php echo $exception->getLine(); ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <span class="text-gray-500 font-medium block mb-0.5">PHP 版本</span>
                            <span class="text-gray-900 font-mono"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500 font-medium block mb-0.5">系统环境</span>
                            <span class="text-gray-900"><?php echo PHP_OS; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 主代码片段（异常发生位置） -->
    <?php if (file_exists($exception->getFile())): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-900">错误位置代码</h2>
                </div>
                <span class="text-xs text-gray-500 font-mono">
                    <?php echo basename($exception->getFile()); ?>:<?php echo $exception->getLine(); ?>
                </span>
            </div>
            <?php echo renderCodeSnippet($exception->getFile(), $exception->getLine(), 6); ?>
        </div>
    <?php endif; ?>

    <!-- 堆栈跟踪 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900">堆栈跟踪</h2>
            </div>
            <span class="text-xs text-gray-400">点击展开代码</span>
        </div>
        <div class="divide-y divide-gray-200">
            <?php foreach ($exception->getTrace() as $index => $trace):
                $hasFile = isset($trace['file']) && isset($trace['line']);
                $isUserCode = $hasFile && isUserCode($trace['file']);
            ?>
                <div class="stack-item group <?php echo $hasFile ? 'cursor-pointer' : ''; ?>"
                     <?php if ($hasFile): ?>onclick="toggleStack(this)"<?php endif; ?>>
                    <div class="p-5 hover:bg-gray-50 flex items-start gap-4 select-none">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full <?php echo $isUserCode ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'; ?> font-medium text-sm transition-colors">
                            <?php echo $index + 1; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <?php if (isset($trace['class'])): ?>
                                    <span class="text-sm font-medium text-gray-900 font-mono">
                                        <?php echo $trace['class'] . $trace['type'] . $trace['function']; ?>()
                                    </span>
                                <?php elseif (isset($trace['function'])): ?>
                                    <span class="text-sm font-medium text-gray-900 font-mono">
                                        <?php echo $trace['function']; ?>()
                                    </span>
                                <?php endif; ?>
                                <?php if ($isUserCode): ?>
                                    <span class="user-code-badge bg-green-100 text-green-700 rounded-full font-medium uppercase tracking-wider">应用代码</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($hasFile): ?>
                                <p class="text-sm text-gray-500 font-mono flex items-center gap-1.5 break-all">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <?php echo $trace['file']; ?>:<?php echo $trace['line']; ?>
                                </p>
                            <?php else: ?>
                                <p class="text-sm text-gray-400">[内部函数]</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($hasFile): ?>
                            <div class="flex-shrink-0 text-gray-400 stack-arrow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasFile): ?>
                        <div class="code-panel border-t border-gray-200">
                            <div class="border-t-2 border-transparent">
                                <?php echo renderCodeSnippet($trace['file'], $trace['line']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 返回顶部按钮 -->
<div class="fixed bottom-6 right-6">
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-3 rounded-lg shadow-lg transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
        返回顶部
    </button>
</div>

<script>
    function toggleStack(element) {
        const panel = element.querySelector('.code-panel');
        const arrow = element.querySelector('.stack-arrow');

        if (!panel) return;

        const isExpanded = panel.classList.contains('expanded');

        // 先关闭所有其他展开的面板（手风琴效果，可选）
        // document.querySelectorAll('.code-panel.expanded').forEach(p => {
        //     if (p !== panel) {
        //         p.classList.remove('expanded');
        //         p.closest('.stack-item').querySelector('.stack-arrow')?.classList.remove('rotated');
        //     }
        // });

        if (isExpanded) {
            panel.classList.remove('expanded');
            arrow?.classList.remove('rotated');
        } else {
            panel.classList.add('expanded');
            arrow?.classList.add('rotated');

            // 自动滚动到代码区域，使其完整可见
            setTimeout(() => {
                const rect = panel.getBoundingClientRect();
                if (rect.bottom > window.innerHeight) {
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 100);
        }
    }
</script>
</body>
</html>