#!/bin/sh

export WP_DEVELOP_DIR="$1"

shift

phpunit "$@"