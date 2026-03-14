<?php
/**
 * Parameter Viewer - Minimalist design with 2FA token integration.
 */

// Merge GET and POST parameters
$params = array_merge($_GET, $_POST);

// Function to fetch 2FA token
function get2FAToken($secret) {
    $secret = trim($secret);
    if (empty($secret)) {
        return "Secret is empty";
    }
    $url = "https://2fa.live/tok/" . urlencode($secret);
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $response = @file_get_contents($url, false, $ctx);
    
    if ($response === false) {
        return "Error fetching token";
    }
    
    $trimmed = trim($response);
    
    // Check if response is JSON (e.g., {"token":"330251"})
    if (strpos($trimmed, '{') === 0) {
        $data = json_decode($trimmed, true);
        if (isset($data['token'])) {
            return $data['token'];
        }
    }
    
    return $trimmed;
}

$hasParams = !empty($params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Deck</title>
    <meta name="description" content="A minimalist utility to view parameters and generate 2FA tokens.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #ffffff;
            --text: #000000;
            --text-sec: #666666;
            --border: #dddddd;
            --highlight: #f5f5f5;
            --accent-bg: #000000;
            --accent-text: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            border-radius: 0 !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            padding: 40px 20px;
            line-height: 1.4;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        header {
            border-bottom: 2px solid var(--text);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-sec);
            margin-top: 4px;
        }

        .item-list {
            border: 1px solid var(--border);
        }

        .item {
            border-bottom: 1px solid var(--border);
            padding: 10px;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .key {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 700;
            background: var(--accent-bg);
            color: var(--accent-text);
            padding: 3px 10px;
            text-transform: uppercase;
        }

        .copy-btn {
            background: var(--bg);
            border: 1px solid var(--text);
            color: var(--text);
            padding: 5px 15px;
            font-size: 9px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
        }

        .copy-btn:hover {
            background: var(--text);
            color: var(--bg);
        }

        .copy-btn.active {
            background: #000;
            color: #fff;
        }

        .value-box {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            background: var(--highlight);
            padding: 5px;
            border: 1px solid var(--border);
            word-break: break-all;
            white-space: pre-wrap;
        }

        .empty {
            padding: 60px 20px;
            text-align: center;
            border: 1px dashed var(--border);
        }

        .btn-link {
            display: inline-block;
            border: 1px solid var(--text);
            color: var(--text);
            padding: 12px 24px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 20px;
        }

        .btn-link:hover {
            background: var(--text);
            color: var(--bg);
        }

        footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: var(--text-sec);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Data Inspector</h1>
        </header>

        <main>
            <?php if ($hasParams): ?>
                <div class="item-list">
                    <?php foreach ($params as $key => $value): ?>
                        <?php 
                            $is2FA = (strtolower($key) === '2fa');
                            $displayValue = $is2FA ? get2FAToken($value) : $value;
                        ?>
                        <div class="item">
                            <div class="item-head">
                                <span class="key"><?php echo htmlspecialchars($key); ?><?php echo $is2FA ? ' (TOKEN)' : ''; ?></span>
                                <button class="copy-btn" onclick="copy(this, '<?php echo addslashes(htmlspecialchars($displayValue)); ?>')">Copy</button>
                            </div>
                            <div class="value-box"><?php echo htmlspecialchars($displayValue); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <p>Waiting for request parameters...</p>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            Link Deck 
        </footer>
    </div>

    <script>
        function copy(btn, text) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerText;
                btn.innerText = 'Copied';
                btn.classList.add('active');
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.remove('active');
                }, 1000);
            }).catch(e => console.error('Copy failed', e));
        }
    </script>
</body>
</html>
