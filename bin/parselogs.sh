#!/bin/bash
find "$1" -name 'access*' -mtime -$2 -exec egrep -i '(POST|OPTIONS) /Microsoft-Server-Activesync\?.*User=' {} ';' | sed 's/.*\[//' | sed 's/ \+.*User=/ /' | sed 's/&ItemId=[^&]*//' | sed 's/&CollectionId=[^&]*//' | sed 's/&Cmd=.*//' | sed 's/&DeviceId=/ /' | perl -pe 's/&DeviceType=([^ &]*).*/ $1/'
find "$1" -name 'access*' -mtime -$2 -exec egrep -i '(POST|OPTIONS) /Microsoft-Server-Activesync\?' {} ';' | grep -v 'User=' | sed 's/.*\[//' | sed 's/ \+.*]//' | sed 's/".*\/Microsoft-Server-ActiveSync?//' | sed 's/ HTTP\/1\..*//'
