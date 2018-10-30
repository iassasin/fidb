#!/bin/sh

phpunit="vendor/bin/phpunit"
coveralls="vendor/bin/php-coveralls"

$phpunit
TRAVIS="1" TRAVIS_JOB_ID="1" $coveralls --dry-run -vvv
