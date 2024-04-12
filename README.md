# Installing

## Setup

### Run app

    sail up

### Create .env

    cp .env.example .env

### Key generate

    sail php artisan key:generate

## Start Project

### Run app

    sail up

### Run Frontend

    sail npm run dev

### Run Reverb

    sail php artisan reverb:start

### Access URL

    http://localhost/

## Clone Project

If you're cloning the project for the first time, execute the following command to initialize the sail build.

    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php83-composer:latest \
        composer install --ignore-platform-reqs
