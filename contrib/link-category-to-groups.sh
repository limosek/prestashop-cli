#!/bin/sh

#######################################################################################################################################
## This script is used as practical guide to prestashop-cli 
## This example will get all categories matched by "$1" and all groups matched by "$2" and link all groups to this categories.
## Because there is no API call for managing this associations, result of this script is SQL script which has to be imported afterwards.
########################################################################################################################################

categoryregexp="$1"
groupregexp="$2"

if [ -z "$2" ]; then
  echo "$0 categoryregexp grouprexexp"
  echo "Use .* to match all of them."
  exit 1
fi 

groups=$(pslist groups "name~$groupregexp")
categories=$(pslist categories "name~$categoryregexp")
#echo "--- Groups: $groups"
#echo "--- Categories: $categories"

echo "BEGIN;"
for c in $categories; do
  for g in $groups; do
	echo "DELETE FROM ps_category_group WHERE id_category=$c AND id_group=$g;"
	echo "INSERT INTO ps_category_group (id_category,id_group) VALUES ($c,$g);"
  done
done
echo "COMMIT;"

