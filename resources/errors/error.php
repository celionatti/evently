<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error - Trees Framework</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header {
            background: rgba(18, 120, 189, 0.9);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            font-size: 2.5rem;
        }

        .header-content h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .header-content p {
            opacity: 0.9;
        }

        .content {
            padding: 2rem;
        }

        .error-section {
            margin-bottom: 2rem;
        }

        .error-section h2 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #f87171;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-section pre {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            padding: 1.25rem;
            overflow-x: auto;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
            white-space: pre-wrap;
            color: #cbd5e1;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            padding: 1.25rem;
            border-left: 4px solid #3b82f6;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: #93c5fd;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card p {
            font-size: 0.95rem;
            color: #e2e8f0;
            word-break: break-all;
        }

        .trace-toggle {
            background: rgba(239, 68, 68, 0.2);
            color: #fecaca;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .trace-toggle:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        .trace-content {
            display: none;
            margin-top: 1rem;
        }

        .trace-content.show {
            display: block;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            opacity: 0.7;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .header {
                padding: 1.25rem;
            }

            .content {
                padding: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">⚠️</div>
            <div class="header-content">
                <h1>Application Bootstrap Error</h1>
                <p>Trees Framework encountered an issue during initialization</p>
            </div>
        </div>

        <div class="content">
            <div class="error-section">
                <h2>🛑 Error Message</h2>
                <pre><?php echo htmlspecialchars($message); ?></pre>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>📁 Error Code</h3>
                    <p><?php echo $code; ?></p>
                </div>

                <div class="info-card">
                    <h3>📏 Line</h3>
                    <p><?php echo $line; ?></p>
                </div>

                <div class="info-card">
                    <h3>⏰ Time</h3>
                    <p><?php echo $time; ?></p>
                </div>

                <div class="info-card">
                    <h3>🌐 Environment</h3>
                    <p style="text-transform: uppercase;"><?php echo $environment; ?></p>
                </div>

                <div class="info-card">
                    <h3>📄 File</h3>
                    <p><?php echo $file; ?></p>
                </div>
            </div>

            <div class="error-section">
                <h2>🔧 Stack Trace</h2>
                <button class="trace-toggle" onclick="toggleTrace()">
                    <span>👁️</span> Toggle Stack Trace
                </button>
                <div class="trace-content" id="traceContent">
                    <pre><?php echo htmlspecialchars($trace); ?></pre>
                </div>
            </div>

            <div class="error-section">
                <h2>🚧 Troubleshooting Steps</h2>
                <pre>1. Check if your .env file exists in the root directory
2. Verify that all required variables are present (DB_DATABASE, DB_USERNAME, DB_CONNECTION, APP_MAINTENANCE)
3. Ensure your .env file has proper read permissions
4. If you recently updated your environment, try restarting your web server
5. Check the error message and stack trace above for specific details</pre>
            </div>

            <div class="action-buttons">
                <a href="#" class="btn btn-primary">
                    <span>📄 View Documentation</span>
                </a>
                <a href="#" class="btn btn-secondary">
                    <span>🐛 Report Issue</span>
                </a>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Trees Framework &copy; <?php echo date('Y'); ?> - Powered by PHP <?php echo PHP_VERSION; ?></p>
    </div>

    <script>
        function toggleTrace() {
            const traceContent = document.getElementById('traceContent');
            traceContent.classList.toggle('show');
        }
    </script>
</body>

</html>