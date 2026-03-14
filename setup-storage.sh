#!/bin/bash
# Run this once after installation to create required storage directories
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p storage/app/public/cvs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "Storage directories created."
