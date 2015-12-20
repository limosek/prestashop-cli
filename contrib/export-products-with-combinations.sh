#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export all products and combinations which have reference 
###########################################################

# Get product option name for given id
getpo() 
{
	psget -Fcli product_option "$1" name
}

# Get product option value for given id
getpov() 
{
	psget product_option_value $* 
}

# Get all combination ids into variable. 
combs=$(pslist combinations 'reference!=')
eval $(pslist -Fenvarr combinations 'reference!=' id_attribute_group price reference weight )

# Products with price=0 are combinations only for me
prods=$(pslist products active=1 'reference!=')
eval $(pslist -Fenvarr products 'reference!=' price reference weight category description name)

for c in $combinations; do
	# Use env output format and eval it. It will set all variables.
  	(eval $(psget -Fenv combination $c)
	eval $(psget -Fenv product $id_product name description id_category_default price)
	pprice=$price
    	option=$(getpov $id_product_option)
    	optionvalue=$(getpov $id_product_option name)
	optionname=$(getpo $(getpov $id_product_option id_attribute_group))
	category=$(psget category $id_category_default name)
	[ "$price" = 0 ] && price=$pprice
    	echo -En "$reference;$name $optionname:$optionvalue;$description;$category;$weight;$price;"
    	pslist stock_availables id_product_attribute=$id quantity | cut -d ' ' -f 2	
    	)
done

for p in $products; do
	# Skip products which were listed in combinations
	if [ -n "$(pslist combinations id_product=$p)" ]; then
	  echo "Skipping product $p because it has combinations" >&2
	  continue
	fi
  	(
	eval $(psget -Fenv product $p )
	category=$(psget category $id_category_default name)
    	echo -En "$reference;$name;$description;$category;$weight;$price;"
    	pslist stock_availables id_product=$p quantity | cut -d ' ' -f 2	
    	)
done

