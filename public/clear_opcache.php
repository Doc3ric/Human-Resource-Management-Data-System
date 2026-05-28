<?php
// Temporary OPcache reset script — DELETE THIS FILE AFTER USE
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache cleared successfully.';
} else {
    echo 'OPcache is not enabled.';
}
