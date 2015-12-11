#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It can be used to import objects from CSV
## If base64 is used, all data are base64 encoded to avoid some shell escaping
###########################################################

if [ -z "$2" ]; then
	echo "$0 objects import.csv [base64]"
	exit 2
fi

if ! which csvtool >/dev/null; then
	echo Please install csvtool package. Exiting.
 	error 1
fi

objects="$1"
csv="$2"
base64="$3"
if [ -n "$base64" ]; then
  base64="--base64"
fi

import_line() {
	local p b64 args desc
	for p in $props; do
		if [ "$p" = "id" ]; then
			shift
			continue
		fi
		b64=$(echo $1 | base64 -w0)
		args="$args $p=$b64"
		desc="$desc $p='$1'"
		shift
	done
	echo "# $desc" >&2
	echo psadd $base64 $objects $args 
}

props=$(psprops $objects)

export -f import_line
export objects props base64
csvtool -t ';' call import_line $csv


