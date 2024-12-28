#!/bin/sh

cd src
composer install --prefer-dist --no-progress
./sass.sh
cp .env.json.dist .env.json.local
mkdir public/uploaded
./fixtures.sh
./tests.sh