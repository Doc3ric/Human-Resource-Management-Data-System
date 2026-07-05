<?php
// One-time cache buster — delete this file after use
if (function_exists('opcache_reset')) {
    opcache_reset();
}
echo 'Cache cleared. You can delete this file now.';
