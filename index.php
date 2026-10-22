<?php
header("Content-Type: text/plain");
require __DIR__ . '/vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Prometheus\Gauge;
use Prometheus\Counter;

$adapter = new InMemory();
$registry = new CollectorRegistry($adapter);

$php_v=PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
# Labels building
if (str_contains(PHP_SAPI, 'cgi')) {
  $pool_info = fpm_get_status();
  $labels = ['php_version', 'php_sapi', 'fpm_pool'];
  $labels_values = [$php_v, PHP_SAPI, $pool_info['pool']];
} else {
  $labels = ['php_version', 'php_sapi'];
  $labels_values = [$php_v, PHP_SAPI];
}

# Span building
$opcacheMemoryUsed = $registry->registerGauge('opcache', 'memory_used', 'memory used', $labels);
$opcacheMemoryFree = $registry->registerGauge('opcache', 'memory_free', 'memory free', $labels);
$opcacheMemoryWasted = $registry->registerGauge('opcache', 'memory_wasted', 'memory wasted', $labels);
$opcacheMemoryWastedPercentage = $registry->registerGauge('opcache', 'memory_wasted_percentage', 'memory wasted_percentage', $labels);

$opcacheStringsMemoryUsed = $registry->registerGauge('opcache', 'interned_string_memory_used', 'interned string memory used', $labels);
$opcacheStringsMemoryFree = $registry->registerGauge('opcache', 'interned_string_memory_free', 'interned string memory free', $labels);

$opcacheHits = $registry->registerCounter('opcache', 'hits_total', 'hits', $labels);
$opcacheMisses = $registry->registerCounter('opcache', 'misses_total', 'misses', $labels);

$opcacheCachedScripts = $registry->registerGauge('opcache', 'cached_scripts', 'cached scripts', $labels);
$opcacheCachedKeys = $registry->registerGauge('opcache', 'cached_keys', 'cached keys', $labels);
$opcacheMaxCachedKeys = $registry->registerGauge('opcache', 'max_cached_keys', 'max cached keys', $labels);
$opcacheHitRate = $registry->registerGauge('opcache', 'hit_rate', 'hit_rate', $labels);

$opcacheEnabled = $registry->registerGauge('opcache', 'enabled', 'opcache enabled', $labels);

# false when opcache is disabled; false skips the per-script list
$opcacheStatus = function_exists('opcache_get_status') ? opcache_get_status(false) : false;
$opcacheEnabled->set(is_array($opcacheStatus) ? 1 : 0, $labels_values);

if (is_array($opcacheStatus)) {

    $opcacheMemoryUsed->set(
        $opcacheStatus['memory_usage']['used_memory'],
        $labels_values
    );

    $opcacheMemoryFree->set(
        $opcacheStatus['memory_usage']['free_memory'],
        $labels_values
    );

    $opcacheMemoryWasted->set(
        $opcacheStatus['memory_usage']['wasted_memory'],
        $labels_values
    );

    $opcacheMemoryWastedPercentage->set(
        $opcacheStatus['memory_usage']['current_wasted_percentage'],
        $labels_values
    );

    $opcacheStringsMemoryUsed->set(
        $opcacheStatus['interned_strings_usage']['used_memory'],
        $labels_values
    );

    $opcacheStringsMemoryFree->set(
        $opcacheStatus['interned_strings_usage']['free_memory'],
        $labels_values
    );

    $opcacheHits->incBy(
        $opcacheStatus['opcache_statistics']['hits'],
        $labels_values
    );

    $opcacheMisses->incBy(
        $opcacheStatus['opcache_statistics']['misses'],
        $labels_values
    );

    $opcacheCachedScripts->set(
        $opcacheStatus['opcache_statistics']['num_cached_scripts'],
        $labels_values
    );

    $opcacheCachedKeys->set(
        $opcacheStatus['opcache_statistics']['num_cached_keys'],
        $labels_values
    );

    $opcacheMaxCachedKeys->set(
        $opcacheStatus['opcache_statistics']['max_cached_keys'],
        $labels_values
    );

    $opcacheHitRate->set(
        $opcacheStatus['opcache_statistics']['opcache_hit_rate'],
        $labels_values
    );

}

$renderer = new RenderTextFormat();

echo $renderer->render($registry->getMetricFamilySamples());
