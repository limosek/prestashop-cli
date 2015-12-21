#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export all products and combinations which have reference 
###########################################################

csvline(){
  local i
  for i in "$@"; do
    echo -En "\"$i\";"
  done
  echo
}

# Get all combination ids into variable. 
combids=$(pslist combinations 'reference!=')
eval $(pslist -Fenvarr combinations 'reference!=' id_product price reference weight)

# Products with price=0 are combinations only for me
prodids=$(pslist products active=1 'reference!=')
eval $(pslist -Fenvarr products 'reference!=' price reference weight id_category_default description_short description name)

# Get all product options
eval $(pslist -Fenvarr product_options name)

# Get all product option values
eval $(pslist -Fenvarr product_option_values name id_attribute_group)

# Get all categories
eval $(pslist -Fenvarr categories name)

for c in $combids; do
	price=${combinations[$c,price]}
	id_product=${combinations[$c,id_product]}
	pprice=${products[$id_product,price]}
	id_product_option=$(psget product $id_product id_product_option)
    	option=${product_options[$c,$id_product_option]}
    	optionvalue=${product_option_values[$id_product_option,name]}
    	id_attribute_group=${product_option_values[$id_product_option,id_attribute_group]}
	optionname=${product_options[$id_attribute_group,name]}
	id_category_default=${products[$id_product,id_category_default]}
	category=${categories[$id_category_default,name]}
	reference=${combinations[$c,reference]}
 	name=${products[$id_product,name]}
 	description=${products[$id_product,description]}
 	[ -z "$description" ] && description=${products[$id_product,description_short]}
 	weight=${combinations[$c,weight]}
 	[ "$price" = 0.000000 ] && price=$pprice
 	if [ "$price" = 0.000000 ]; then 
	  echo "Zero price?? id_proruct=$id_product"; continue
	fi
	csvline "$reference" "$name $optionname:$optionvalue" "$description" "$category" "$weight" "$price" $(pslist stock_availables id_product_attribute=$c quantity | cut -d ' ' -f 2)
done

for p in $prodids; do
	# Skip products which were listed in combinations
	if [ -n "$(pslist combinations id_product=$p)" ]; then
	  echo "Skipping product $p because it has combinations" >&2
	  continue
	fi
  	(
	eval $(psget -Fenv product $p )
	category=${categories[$id_category_default,name]}
	[ -z "$description" ] && description=$description_short}
    	csvline "$reference" "$name" "$description" "$category" "$weight" "$price" $(pslist stock_availables id_product=$p quantity | cut -d ' ' -f 2)
    	)
done

