<?php

return [
    'node_binary' => env('BROWSERSHOT_NODE_BINARY', '/usr/bin/node'),
    'npm_binary' => env('BROWSERSHOT_NPM_BINARY', '/usr/bin/npm'),
    'chrome_path' => env('BROWSERSHOT_CHROME_PATH', '/usr/bin/chromium'),
];
