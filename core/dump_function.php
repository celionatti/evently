<?php

declare(strict_types=1);

use Trees\Dumper\Dumper;

/**
 * Modern PHP Dump & Debugging Tool
 *
 * Features:
 * - Clean, readable output with improved color scheme
 * - Collapsible sections for better navigation
 * - Search functionality
 * - Memory usage tracking
 * - Responsive design
 * - Production-friendly (won't break your app)
 */
if (!function_exists('dd')) {
    function dd(...$vars): void {
        Dumper::dump(...$vars);
        // dump_modern(...$vars);
        die();
    }
}

if (!function_exists('dump_modern')) {
    function dump_modern(...$vars): void {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'unknown';
        $line = $backtrace[0]['line'] ?? 'unknown';

        echo generateDumpHTML($vars, $file, $line);
    }
}

function generateDumpHTML(array $vars, string $file, int|string $line): string {
    $timestamp = date('Y-m-d H:i:s');
    $varCount = count($vars);
    $memUsage = formatBytes(memory_get_usage());
    $peakMem = formatBytes(memory_get_peak_usage());

    ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Debug Dump</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', 'Roboto Mono', monospace;
            background: #f8fafc;
            min-height: 100vh;
            padding: 20px;
            color: #1e293b;
            line-height: 1.5;
        }

        .dump-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .header {
            background: #4f46e5;
            color: white;
            padding: 20px 25px;
            position: relative;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 13px;
        }

        .file-info {
            opacity: 0.9;
            font-family: monospace;
        }

        .timestamp {
            opacity: 0.9;
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 8px;
            border-radius: 12px;
        }

        .controls {
            padding: 15px 20px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
            padding: 8px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            font-size: 14px;
            background: white;
            color: #1e293b;
            transition: all 0.2s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-box::placeholder {
            color: #94a3b8;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-expand {
            background: #10b981;
            color: white;
        }

        .btn-collapse {
            background: #3b82f6;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .content {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .content::-webkit-scrollbar {
            width: 6px;
        }

        .content::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .var-section {
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .var-header {
            background: #f1f5f9;
            color: #1e293b;
            padding: 12px 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
            border-bottom: 1px solid #e2e8f0;
        }

        .var-header:hover {
            background: #e2e8f0;
        }

        .var-body {
            padding: 16px;
            background: white;
        }

        .toggle-icon {
            transition: transform 0.2s ease;
            font-size: 14px;
            color: #64748b;
        }

        .var-section.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }

        .var-section.collapsed .var-body {
            display: none;
        }

        .dump-output {
            background: #f8fafc;
            color: #1e293b;
            padding: 16px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            position: relative;
            border: 1px solid #e2e8f0;
            font-family: monospace;
            white-space: pre;
        }

        .dump-output::before {
            content: 'PHP';
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 10px;
            color: #64748b;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .string { color: #059669; }
        .number { color: #d97706; }
        .boolean { color: #7c3aed; }
        .null { color: #64748b; }
        .key { color: #2563eb; }
        .type { color: #0e7490; }
        .bracket { color: #9f1239; }
        .arrow { color: #c026d3; }
        .comment { color: #64748b; font-style: italic; }

        .highlight {
            background: #fde047;
            color: #1e293b;
            padding: 1px 3px;
            border-radius: 2px;
        }

        .stats {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 20px;
            text-align: center;
            font-size: 13px;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            body { padding: 12px; }
            .header { padding: 16px; }
            .content { padding: 16px; }
            .controls { padding: 12px; }
        }
    </style>
</head>
<body>
    <div class="dump-container">
        <div class="header">
            <h1>🔍 PHP Debug Dump</h1>
            <div class="header-info">
                <div class="file-info">📁 <?= htmlspecialchars($file) ?>:<?= $line ?></div>
                <div class="timestamp">⏰ <?= $timestamp ?></div>
            </div>
        </div>

        <div class="controls">
            <input type="text" class="search-box" placeholder="🔍 Search in dump..." id="searchBox">
            <button class="btn btn-expand" onclick="expandAll()">Expand All</button>
            <button class="btn btn-collapse" onclick="collapseAll()">Collapse All</button>
        </div>

        <div class="content" id="content">
            <?php foreach ($vars as $index => $var): ?>
                <?php
                    $varNum = $index + 1;
                    $type = gettype($var);
                    $size = getVariableSize($var);
                ?>
                <div class="var-section" data-index="<?= $index ?>">
                    <div class="var-header" onclick="toggleSection(<?= $index ?>)">
                        <span>Variable #<?= $varNum ?> (<?= $type ?>) - <?= $size ?></span>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="var-body">
                        <div class="dump-output"><?= formatVariable($var) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="stats">
            📊 Variables: <?= $varCount ?> | 💾 Memory: <?= $memUsage ?> | 📈 Peak: <?= $peakMem ?>
        </div>
    </div>

    <script>
        function toggleSection(index) {
            const section = document.querySelector(`[data-index="${index}"]`);
            section.classList.toggle("collapsed");
        }

        function expandAll() {
            document.querySelectorAll(".var-section").forEach(section => {
                section.classList.remove("collapsed");
            });
        }

        function collapseAll() {
            document.querySelectorAll(".var-section").forEach(section => {
                section.classList.add("collapsed");
            });
        }

        document.getElementById("searchBox").addEventListener("input", function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const sections = document.querySelectorAll(".var-section");

            sections.forEach(section => {
                const content = section.textContent.toLowerCase();
                if (searchTerm === "" || content.includes(searchTerm)) {
                    section.style.display = "block";
                    if (searchTerm !== "") {
                        highlightText(section, searchTerm);
                    } else {
                        removeHighlight(section);
                    }
                } else {
                    section.style.display = "none";
                }
            });
        });

        function highlightText(element, searchTerm) {
            const dumpOutput = element.querySelector(".dump-output");
            let content = dumpOutput.innerHTML;
            content = content.replace(/<span class="highlight">(.*?)<\/span>/g, "$1");
            const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, "gi");
            content = content.replace(regex, '<span class="highlight">$1</span>');
            dumpOutput.innerHTML = content;
        }

        function removeHighlight(element) {
            const dumpOutput = element.querySelector(".dump-output");
            let content = dumpOutput.innerHTML;
            content = content.replace(/<span class="highlight">(.*?)<\/span>/g, "$1");
            dumpOutput.innerHTML = content;
        }

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        }

        // Expand first section by default
        document.addEventListener("DOMContentLoaded", function() {
            const firstSection = document.querySelector(".var-section");
            if (firstSection) {
                firstSection.classList.remove("collapsed");
            }
        });
    </script>
</body>
</html>
<?php
    return ob_get_clean();
}

function formatVariable($var, $depth = 0, $maxDepth = 10): string {
    if ($depth > $maxDepth) {
        return '<span class="comment">// Max depth reached</span>';
    }

    $indent = str_repeat('  ', $depth);
    $type = gettype($var);

    switch ($type) {
        case 'string':
            $escaped = htmlspecialchars($var);
            return '<span class="type">string</span>(<span class="number">'.strlen($var).'</span>) <span class="string">"'.$escaped.'"</span>';

        case 'integer':
            return '<span class="type">int</span>(<span class="number">'.$var.'</span>)';

        case 'double':
            return '<span class="type">float</span>(<span class="number">'.$var.'</span>)';

        case 'boolean':
            return '<span class="type">bool</span>(<span class="boolean">'.($var ? 'true' : 'false').'</span>)';

        case 'NULL':
            return '<span class="null">NULL</span>';

        case 'array':
            $count = count($var);
            $output = '<span class="type">array</span>(<span class="number">'.$count.'</span>) <span class="bracket">{</span>'."\n";

            foreach ($var as $key => $value) {
                $keyStr = is_string($key)
                    ? '<span class="string">"'.htmlspecialchars($key).'"</span>'
                    : '<span class="number">'.$key.'</span>';

                $output .= $indent.'  <span class="bracket">[</span>'.$keyStr.'<span class="bracket">]</span> <span class="arrow">=></span>'."\n";
                $output .= $indent.'  '.formatVariable($value, $depth + 1, $maxDepth)."\n";
            }

            return $output.$indent.'<span class="bracket">}</span>';

        case 'object':
            $className = get_class($var);
            $reflection = new ReflectionClass($var);
            $output = '<span class="type">object</span>(<span class="key">'.$className.'</span>) <span class="bracket">{</span>'."\n";

            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                $visibility = $property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : 'private');

                $output .= $indent.'  <span class="comment">// '.$visibility.'</span>'."\n";
                $output .= $indent.'  <span class="key">'.$property->getName().'</span> <span class="arrow">=></span>'."\n";
                $output .= $indent.'  '.formatVariable($property->getValue($var), $depth + 1, $maxDepth)."\n";
            }

            return $output.$indent.'<span class="bracket">}</span>';

        case 'resource':
            return '<span class="type">resource</span>(<span class="key">'.get_resource_type($var).'</span>)';

        default:
            return '<span class="type">'.$type.'</span>';
    }
}

function getVariableSize($var): string {
    return formatBytes(strlen(serialize($var)));
}

function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision).' '.$units[$pow];
}