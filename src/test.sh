#!/usr/bin/env bash

find . -name '*.php' -print0 | xargs -0 -L 1 php -l