#!/bin/sh

#########################################################################
## This script is used as practical guide to prestashop-cli 
## It will find all product with given tax rule and nex to this
## it will update that products to not use tax at all and multiply price
#########################################################################

taxrule="$1"
multiply="$2"
if [ -z "$2" ]; then
  echo "$0 taxid multiply"
  exit 1
fi 

products=$(pslist products id_tax_rules_group=$taxrule)
for p in $products; do
	echo psupdate product $p id_tax_rules_group=0 "price*=$multiply"
	for c in $(pslist combinations id_product=$p); do
		echo psupdate combination $c "price*=$multiply"
	done
done


