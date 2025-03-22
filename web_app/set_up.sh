#!/bin/sh

composer install --prefer-dist --no-progress
./sass.sh
cp -n .env.json.dist .env.json.local
mkdir -p public/uploaded
./scripts/fixtures.php --recreate
./tests.sh