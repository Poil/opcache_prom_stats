<?php
header('Content-Type: text/plain; version=0.0.4');

# Labels building
$labels = [
    'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
    'php_sapi' => PHP_SAPI,
];
if (str_contains(PHP_SAPI, 'cgi')) {
    $labels['fpm_pool'] = fpm_get_status()['pool'];
}
$labels = implode(',', array_map(
    fn ($k, $v) => $k . '="' . addcslashes($v, "\\\"\n") . '"',
    array_keys($labels),
    $labels
));

function metric(string $name, string $type, string $help, $value): void
{
    global $labels;
    echo "# HELP opcache_$name $help\n# TYPE opcache_$name $type\nopcache_$name{" . $labels . "} $value\n";
}

# false when opcache is disabled; false skips the per-script list
$status = function_exists('opcache_get_status') ? opcache_get_status(false) : false;
metric('enabled', 'gauge', 'opcache enabled', is_array($status) ? 1 : 0);

if (is_array($status)) {
    $memory = $status['memory_usage'];
    $strings = $status['interned_strings_usage'];
    $stats = $status['opcache_statistics'];

    metric('memory_used', 'gauge', 'memory used', $memory['used_memory']);
    metric('memory_free', 'gauge', 'memory free', $memory['free_memory']);
    metric('memory_wasted', 'gauge', 'memory wasted', $memory['wasted_memory']);
    metric('memory_wasted_percentage', 'gauge', 'memory wasted_percentage', $memory['current_wasted_percentage']);

    metric('interned_string_memory_used', 'gauge', 'interned string memory used', $strings['used_memory']);
    metric('interned_string_memory_free', 'gauge', 'interned string memory free', $strings['free_memory']);

    metric('hits_total', 'counter', 'hits', $stats['hits']);
    metric('misses_total', 'counter', 'misses', $stats['misses']);

    metric('cached_scripts', 'gauge', 'cached scripts', $stats['num_cached_scripts']);
    metric('cached_keys', 'gauge', 'cached keys', $stats['num_cached_keys']);
    metric('max_cached_keys', 'gauge', 'max cached keys', $stats['max_cached_keys']);
    metric('hit_rate', 'gauge', 'hit_rate', $stats['opcache_hit_rate']);
}
