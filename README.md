1. Remove the .dist extension from the files that bear it, and fill them in with real values.
2. docker-compose up
3. From the web container, run composer install.
4. From the web container, run vendor/bin/pscss sass/style.scss public/style.css