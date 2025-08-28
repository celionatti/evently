<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        :root {
            --primary: #6366f1;
            --danger: #ef4444;
            --background: #f9fafb;
            --card: #ffffff;
            --text: #111827;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            color: var(--text);
            background-color: var(--background);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .error-card {
            background: var(--card);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        h1 {
            color: var(--danger);
            margin-top: 0;
        }

        pre {
            background: #1e293b;
            color: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Consolas', monospace;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .solution {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 10px 15px;
            margin-bottom: 10px;
        }

        .stack-frame {
            margin-bottom: 15px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 4px;
        }

        .stack-frame-file {
            font-weight: 600;
            color: var(--primary);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --background: #1a1a1a;
                --card: #2d2d2d;
                --text: #f5f5f5;
                --text-muted: #a3a3a3;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><strong><?= htmlspecialchars($exception->getMessage()) ?></strong></p>
            <p>In <code><?= htmlspecialchars($exception->getFile()) ?></code> on line <code><?= $exception->getLine() ?></code></p>
        </div>

        <?php if (!empty($solutions)): ?>
        <div class="error-card">
            <h2 class="section-title">Suggested Solutions</h2>
            <?php foreach ($solutions as $solution): ?>
                <div class="solution"><?= htmlspecialchars($solution) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="error-card">
            <h2 class="section-title">Stack Trace</h2>
            <?php foreach ($exception->getTrace() as $i => $frame): ?>
                <div class="stack-frame">
                    <div class="stack-frame-file">#<?= $i ?> <?= $frame['file'] ?? '[internal function]' ?>:<?= $frame['line'] ?? '' ?></div>
                    <div><?= $frame['class'] ?? '' ?><?= $frame['type'] ?? '' ?><?= $frame['function'] ?? '' ?>()</div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="error-card">
            <h2 class="section-title">Request Data</h2>
            <pre><?= htmlspecialchars(json_encode($requestData, JSON_PRETTY_PRINT)) ?></pre>
        </div>

        <div class="error-card">
            <h2 class="section-title">Environment</h2>
            <pre><?= htmlspecialchars(json_encode($environment, JSON_PRETTY_PRINT)) ?></pre>
        </div>
    </div>
</body>
</html>