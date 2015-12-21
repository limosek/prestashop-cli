#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will list all product combinations and values
###########################################################

# Get product id and name. Parameter is id of product
getproduct() 
{
	psget -Fcli2 product "$1" name
}

# Get product option name for given id
getpo() 
{
	psget -Fcli2 product_option "$1" name
}

# Get product option value for given id
getpov() 
{
	psget product_option_value $* 
}

# Get all combination ids into variable. 
combinations=$(pslist combinations)

for c in $combinations; do
	# Use env output format and eval it. It will set all variables.
  	eval $(psget -Fenv combination $c)
	product=$(getproduct $id_product)
    	option=$(getpov $id_product_option_value)
    	optionvalue=$(getpov $id_product_option_value name)
	optionname=$(getpo $(getpov $id_product_option_value id_attribute_group))
    	echo $product $price "$optionname:$optionvalue" \
		$(pslist stock_availables id_product_attribute=$id quantity | cut -d ' ' -f 2)
done

