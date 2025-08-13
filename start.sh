#!/bin/bash

# using internal server for testing and redirect stdout and stderr to /dev/null to hide output
php -S 127.0.0.1:9000 -t /var/www/html/internal/ >/dev/null 2>&1 &

# 啟動 Apache
apache2-foreground
