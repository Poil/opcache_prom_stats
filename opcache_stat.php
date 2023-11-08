<?php

require __DIR__ . '/vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Prometheus\Gauge;
use Prometheus\Counter;

$adapter = new InMemory();
$registry = new CollectorRegistry($adapter);

$opcacheMemoryUsed = $registry->registerGauge('opcache', 'memory_used', 'memory used', ['bytes']);
$opcacheMemoryFree = $registry->registerGauge('opcache', 'memory_free', 'memory used', ['bytes']);
$opcacheMemoryWasted = $registry->registerGauge('opcache', 'memory_wasted', 'memory wasted', ['bytes']);
$opcacheMemoryWastedPercentage = $registry->registerGauge('opcache', 'memory_wasted_percentage', 'memory wasted_percentage', ['percent']);

$opcacheStringsMemoryUsed = $registry->registerGauge('opcache', 'interned_string_memory_used', 'interned string memory used', ['bytes']);
$opcacheStringsMemoryFree = $registry->registerGauge('opcache', 'interned_string_memory_free', 'interned string memory used', ['bytes']);

$opcacheHits = $registry->registerGauge('opcache', 'hits', 'hits', ['count']);
$opcacheMisses = $registry->registerGauge('opcache', 'misses', 'misses', ['count']);

$opcacheCachedScripts = $registry->registerGauge('opcache', 'cached_scripts', 'cached scripts', ['count']);
$opcacheCachedKeys = $registry->registerGauge('opcache', 'cached_keys', 'cached keys', ['count']);
$opcacheMaxCachedKeys = $registry->registerGauge('opcache', 'max_cached_keys', 'max cached keys', ['count']);
$opcacheHitRate = $registry->registerGauge('opcache', 'hit_rate', 'hit_rate', ['percent']);

$opcacheStatus = opcache_get_status();

$opcacheMemoryUsed->set(
    $opcacheStatus['memory_usage']['used_memory'],
    [$opcacheStatus['memory_usage']['used_memory']]
);

$opcacheMemoryFree->set(
    $opcacheStatus['memory_usage']['free_memory'],
    [$opcacheStatus['memory_usage']['free_memory']]
);

$opcacheMemoryWasted->set(
    $opcacheStatus['memory_usage']['wasted_memory'],
    [$opcacheStatus['memory_usage']['wasted_memory']]
);

$opcacheMemoryWastedPercentage->set(
    $opcacheStatus['memory_usage']['current_wasted_percentage'],
    [$opcacheStatus['memory_usage']['current_wasted_percentage']]
);

$opcacheStringsMemoryUsed->set(
    $opcacheStatus['interned_strings_usage']['used_memory'],
    [$opcacheStatus['interned_strings_usage']['used_memory']]
);

$opcacheStringsMemoryFree->set(
    $opcacheStatus['interned_strings_usage']['free_memory'],
    [$opcacheStatus['interned_strings_usage']['free_memory']]
);

$opcacheStringsMemoryUsed->set(
    $opcacheStatus['interned_strings_usage']['used_memory'],
    [$opcacheStatus['interned_strings_usage']['used_memory']]
);

$opcacheStringsMemoryFree->set(
    $opcacheStatus['interned_strings_usage']['free_memory'],
    [$opcacheStatus['interned_strings_usage']['free_memory']]
);

$opcacheHits->set(
    $opcacheStatus['opcache_statistics']['hits'],
    [$opcacheStatus['opcache_statistics']['hits']]
);

$opcacheMisses->set(
    $opcacheStatus['opcache_statistics']['misses'],
    [$opcacheStatus['opcache_statistics']['misses']]
);

$opcacheCachedScripts->set(
    $opcacheStatus['opcache_statistics']['num_cached_scripts'],
    [$opcacheStatus['opcache_statistics']['num_cached_scripts']]
);

$opcacheCachedKeys->set(
    $opcacheStatus['opcache_statistics']['num_cached_keys'],
    [$opcacheStatus['opcache_statistics']['num_cached_keys']]
);

$opcacheMaxCachedKeys->set(
    $opcacheStatus['opcache_statistics']['max_cached_keys'],
    [$opcacheStatus['opcache_statistics']['max_cached_keys']]
);

$opcacheHitRate->set(
    $opcacheStatus['opcache_statistics']['opcache_hit_rate'],
    [$opcacheStatus['opcache_statistics']['opcache_hit_rate']]
);

$renderer = new RenderTextFormat();

echo $renderer->render($registry->getMetricFamilySamples());
