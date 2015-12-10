#!/bin/sh

#########################################################################
## This script is used as practical guide to prestashop-cli 
## It will find all product and their combinations with given tax rule and next to this
## it will update that products to use new tax rule and multiply price
#########################################################################

taxrule1="$1"
taxrule2="$2"
multiply="$3"
if [ -z "$2" ]; then
  echo "$0 taxid1 taxid2 multiply"
  exit 1
fi 

products=$(pslist products id_tax_rules_group=$taxrule1)
for p in $products; do
	echo psupdate product $p id_tax_rules_group=\"$taxrule2\" \"price*=$multiply\"
	for c in $(pslist combinations id_product=$p); do
		echo psupdate combination $c \"price*=$multiply\"
	done
done


