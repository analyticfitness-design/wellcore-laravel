<?php
/**
 * Ensures nginx has WebSocket proxy blocks for Laravel Reverb.
 * Run on every deploy via silvia-gitpull-load.
 */
$configPath = '/etc/nginx/sites-enabled/default';
$content = file_get_contents($configPath);

if (strpos($content, 'location /app/') !== false) {
    echo "nginx_ws_ok (already patched)\n";
    exit(0);
}

$wsBlocks = <<<'NGINX'

    # WebSocket proxy for Laravel Reverb
    location /app/ {
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_cache_bypass $http_upgrade;
        proxy_pass http://127.0.0.1:8080;
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
    }

    location /apps/ {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_pass http://127.0.0.1:8080;
    }

NGINX;

$marker = '    location ~ \.php$ {';
if (strpos($content, $marker) === false) {
    echo "nginx_error: PHP location block not found\n";
    exit(1);
}

$patched = str_replace($marker, $wsBlocks . $marker, $content);
file_put_contents($configPath, $patched);

passthru('nginx -t 2>&1 && nginx -s reload 2>&1 && echo nginx_reloaded');
echo "nginx_ws_patched\n";
