<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统错误 | <?php echo get_class($exception); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="lg:container mx-auto p-4 lg:p-8">
    <!-- 错误概览卡片 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">
                    <?php echo get_class($exception); ?>
                </h1>
                <span class="px-3 py-1 text-sm text-red-600 bg-red-50 rounded-full">
                        <?php echo $exception->getCode(); ?>
                    </span>
            </div>
        </div>

        <div class="p-6">
            <div class="text-gray-800 text-lg font-medium mb-4">
                <?php echo $exception->getMessage(); ?>
            </div>

            <div class="text-sm text-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="mb-1">
                            <span class="font-medium">文件位置:</span>
                            <?php echo $exception->getFile(); ?>
                        </p>
                        <p>
                            <span class="font-medium">错误行号:</span>
                            <?php echo $exception->getLine(); ?>
                        </p>
                    </div>
                    <div>
                        <p class="mb-1">
                            <span class="font-medium">PHP 版本:</span>
                            <?php echo PHP_VERSION; ?>
                        </p>
                        <p>
                            <span class="font-medium">系统环境:</span>
                            <?php echo PHP_OS; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 代码预览 -->
    <?php if (file_exists($exception->getFile())): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">代码片段</h2>
            </div>
            <div class="p-6 bg-gray-900 overflow-x-auto">
                <?php
                $file  = file($exception->getFile());
                $line  = $exception->getLine();
                $start = max($line - 5, 0);
                $end   = min($line + 5, count($file));
                ?>
                <pre class="text-sm">
                    <code>
                        <?php
                        for ($i = $start; $i < $end; $i++) {
                            $currentLine = $i + 1;
                            $class       = $currentLine == $line ? 'bg-red-900/50' : '';
                            echo '<div class="' . $class . ' px-4 py-0.5">';
                            echo '<span class="inline-block w-12 text-gray-500">' . $currentLine . '</span>';
                            echo '<span class="text-gray-300">' . htmlspecialchars($file[$i]) . '</span>';
                            echo '</div>';
                        }
                        ?>
                    </code>
                </pre>
            </div>
        </div>
    <?php endif; ?>

    <!-- 堆栈跟踪 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">堆栈跟踪</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php foreach ($exception->getTrace() as $index => $trace): ?>
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600">
                            <?php echo $index + 1; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <?php if (isset($trace['class'])): ?>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $trace['class'] . $trace['type'] . $trace['function']; ?>
                                </p>
                            <?php endif; ?>
                            <?php if (isset($trace['file'])): ?>
                                <p class="text-sm text-gray-600">
                                    <?php echo $trace['file'] . ':' . $trace['line']; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 返回按钮 -->
<div class="fixed bottom-6 right-6">
    <div class="fixed bottom-6 right-6">
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                class="bg-gray-900 hover:bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg transition-colors">
            返回顶部
        </button>
    </div>
</div>
</body>
</html>