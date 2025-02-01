#!/bin/sh

composer install --prefer-dist --no-progress
./sass.sh
cp .env.json.dist .env.json.local
mkdir public/uploaded
./scripts/fixtures.php --recreate
./tests.sh