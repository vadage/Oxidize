FROM php:8.2-fpm-alpine AS base

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /oxidize
