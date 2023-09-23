<?php

return [
    'node_path' => env('BROWSERSHOT_NODE_PATH', '/opt/bitnami/node/bin/node'),
    'npm_path' => env('BROWSERSHOT_NPM_PATH', '/opt/bitnami/node/bin/npm'),
    'chrome_path' => env('BROWSERSHOT_CHROME_PATH', '/usr/bin/google-chrome'),
];
