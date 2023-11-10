# Opcache Prometheus Stats
The goal of this project is to have a native PHP page to display Opcache stats in Prometheus Format and avoid any "Go, Python, ...) exporter.

## Installation

```
git clone (...)
cd opcache_stats
composer install
```

If you want an artifact
```
tar --exclude-vcs -zcvf opcache_stats.tar.gz opcache_stats
```

## Configuration

### Apache HTTPd Server
* Example of configuration on localhost with multiple FPM Pool

```
<VirtualHost *:80>
    ServerName default

    ServerAdmin root@poil.fr

    DocumentRoot /srv/www/monitoring
    <Directory /srv/www/monitoring>
       AllowOverride None
       Require all granted
    </Directory>

    <Location "/pl-8.1-test1-opcache-status">
      Require all denied
      Require ip 127.0.0.1
      Alias "/srv/www/monitoring/opcache_stats/index.php"
      SetHandler "proxy:unix:/run/php/php8.1-fpm.test1.sock|fcgi://test1"
    </Location>

    <Location "/pl-8.1-test2-opcache-status">
      Require all denied
      Require ip 127.0.0.1
      Alias "/srv/www/monitoring/opcache_stats/index.php"
      SetHandler "proxy:unix:/run/php/php8.1-fpm.test2.sock|fcgi://test2"
    </Location>
</VirtualHost>
```

### Prometheus / Opentelemetry Agent
* It looks like mandatory to have a Job per pool because `metrics_path` is per job, if anyone have better idea he can do a PR :)
```
- job_name: opcache-test1
  metrics_path: /pl-8.1-test1-opcache-status
  scrape_interval: 1m
  static_configs:
    - targets: ['127.0.0.1:80']
- job_name: opcache-test2
  metrics_path: /pl-8.1-test2-opcache-status
  scrape_interval: 1m
  static_configs:
    - targets: ['127.0.0.1:80']
```
