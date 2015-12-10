#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will list all products with given tax_rules_group
###########################################################

taxrule="$1"
if [ -z "$1" ]; then
  taxrules=$(pslist tax_rule_groups)
  for tr in $taxrules; do
     echo "####### Products with tax rule ${tr} (" $(psget tax_rule_group $tr name) "):"
     pslist products id_tax_rules_group=$tr id name
  done
  echo "####### Products without tax:"
  pslist products id_tax_rules_group=0 id name
else
  pslist products id_tax_rules_group=$taxrule id name
fi 



