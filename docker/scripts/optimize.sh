#!/bin/bash
php artisan config:cache
php artisan route:cache
# No usar view:cache en desarrollo
php artisan optimize:clear
