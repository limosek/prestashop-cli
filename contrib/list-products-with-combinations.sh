#!/bin/sh

getproduct() 
{
	psget -Fcli2 product "$1" name
}

getpo() 
{
	psget -Fcli2 product_option "$1" name
}

getpov() 
{
	psget product_option_value $* 
}

combinations=$(pslist combinations)
for c in $combinations; do
  	eval $(psget -Fenv combination $c)
	product=$(getproduct $id_product)
    	option=$(getpov $id_product_option)
    	optionvalue=$(getpov $id_product_option name)
	optionname=$(getpo $(getpov $id_product_option id_attribute_group))
    	echo $product "$optionname:$optionvalue"
done

