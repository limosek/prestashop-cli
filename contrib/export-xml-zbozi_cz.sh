#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will generate XML feed for zbozi.cz
###########################################################

xmlproduct(){
cat <<EOF
<SHOPITEM>
 <ITEM_ID>$1</ITEM_ID>
 <PRODUCTNAME>${products[$p,name]}</PRODUCTNAME>
 <DESCRIPTION>$(php -r "echo strip_tags('${products[$p,description]}');")</DESCRIPTION>
 <CATEGORYTEXT>$category</CATEGORYTEXT>
EOF
if [ "${products[$p,ean13]}" != 0 ]; then
  echo "<EAN>${products[$p,ean13]}</EAN>"
fi
cat <<EOF
 <PRODUCTNO>$1</PRODUCTNO>
 <MANUFACTURER>${products[$p,manufacturer_name]}</MANUFACTURER>
 <URL>$product_url</URL>
 <IMGURL>$img_url</IMGURL>
 <PRICE_VAT>${products[$p,price]}</PRICE_VAT>
 $2
 </SHOPITEM>
EOF
}

xmlheader()
{
cat <<EOF
<?xml version="1.0" encoding="utf-8"?>
<SHOP xmlns="http://www.zbozi.cz/ns/offer/1.0">
EOF
}

xmltrailer()
{
cat <<EOF
 </SHOP>
EOF
}

log()
{
	echo $* >&2
}

getpo() 
{
	echo ${product_options[$1,name]}
}

# Get product option value for given id
getpov() 
{
	echo ${product_option_values[$1,$2]} 
}

if [ -z "$1" ]; then
	echo $0 shop-url
	exit 2
else
	shop_url="$1"
	shift
fi

filter="$@"

# Get all combination ids into variable. 
log "Getting combinations"
combids=$(pslist combinations 'reference!=')
eval $(pslist -Fenvarr '--delete-characters=;"' combinations 'reference!=' id_product price reference weight)

# Products with price=0 are combinations only for me
log "Getting products (filter=$filter)"
prodids=$(pslist products active=1 'reference!=' $filter)
eval $(pslist -Fenvarr '--delete-characters=;"' products 'reference!=' $filter price reference weight id_category_default description_short description name ean13 manufacturer_name id_default_image)

# Get all product options
log "Getting product options"
eval $(pslist -Fenvarr '--delete-characters=;"' product_options name)

# Get all product option values
log "Getting product option values"
eval $(pslist -Fenvarr '--delete-characters=;"' product_option_values name id_attribute_group position)

# Get all categories
log "Getting categories"
eval $(pslist -Fenvarr '--delete-characters=;"' categories name)

product2comb()
{
	p="$1"
	combinations=$(pslist combinations id_product=$p)
	for c in $combinations; do
		log " Combination $c of product $p"
		price=${combinations[$c,price]}
		id_default_image=${products[$p,id_default_image]}
		id_category_default=${products[$p,id_category_default]}
		category=${categories[$id_category_default,name]}
 		[ -z "$description" ] && description=${products[$p,description_short]}
		category=${categories[$id_category_default,name]}
		[ -z "$description" ] && description=${products[$p,description_short]}
		id_product_option_value=$(psget combination $c id_product_option_value)
		product_url="$shop_url/index.php?controller=product&amp;id_product=$p"
		img_url="$shop_url/$id_default_image-large_default/img.jpg"
		for o in $(echo $id_product_option_value | tr ',' ' '); do
			optionvalue=$(getpov $o name)
			optionname=$(getpo $(getpov $o id_attribute_group))
			options="$options<PARAM><PARAM_NAME>$optionname</PARAM_NAME><VAL>$optionvalue</VAL></PARAM>"	
		done
	done
	xmlproduct ${combinations[$p,reference]} "$options"
}

xmlheader

for p in $prodids; do
	log "Product $p"
	if [ -n "$(pslist combinations id_product=$p)" ]; then
	  product2comb $p
	  continue
	fi
  	(
	id_category_default=${products[$p,id_category_default]}
	category=${categories[$id_category_default,name]}
 	[ -z "$description" ] && description=${products[$p,description_short]}
	category=${categories[$id_category_default,name]}
	[ -z "$description" ] && description=${products[$p,description_short]}
	product_url="$shop_url/index.php?controller=product&amp;id_product=$p"
	img_url="$shop_url/$id_default_image-large_default/img.jpg"
    	xmlproduct ${products[$p,reference]}
    	)
done

xmltrailer

