#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It can be used to export objects to CSV
## If base64 is used, all data are base64 encoded to avoid some shell escaping
###########################################################

if [ -z "$2" ]; then
	echo "$0 object export.csv [base64]"
	exit 2
fi

objects="$1"
csv="$2"
base64="$3"
if [ -n "$base64" ]; then
  base64="--base64"
fi

props=$(psprops $objects)

pslist -Fcsv $base64 $objects $props >$csv

