#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will list all products with given tax_rules_group
###########################################################

taxrule="$1"
if [ -z "$1" ]; then
  echo "$0 taxid"
  exit 1
fi 

pslist products id_tax_rules_group=$taxrule id name


