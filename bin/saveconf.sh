#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cp /etc/mod-security2/*.dat "$DIR/../data/save/"
chown -R www-data: "$DIR/../data/save/"
chmod -R ug+rw "$DIR/../data/save/"
