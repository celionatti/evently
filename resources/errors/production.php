<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $statusCode ?> <?= htmlspecialchars($statusText) ?></title>
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
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }

        .error-container {
            max-width: 500px;
            padding: 2rem;
        }

        h1 {
            font-size: 3rem;
            margin: 0;
            color: var(--danger);
        }

        .status-code {
            font-weight: 700;
        }

        .error-id {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 2rem;
        }

        .home-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .home-link:hover {
            text-decoration: underline;
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
    <div class="error-container">
        <h1><span class="status-code"><?= $statusCode ?></span></h1>
        <p><?= htmlspecialchars($statusText) ?></p>
        <p>Sorry, something went wrong. Our team has been notified.</p>
        <a href="/" class="home-link">Return to Homepage</a>
        <div class="error-id">Error ID: <?= $errorId ?></div>
    </div>
</body>
</html>