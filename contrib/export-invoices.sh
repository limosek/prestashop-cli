#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export invoices
## 
## Arguments are filter(s) to match orders
###########################################################

if [ -z "$2" ]; then
  echo $0 "invoice_type invoice_prefix [filter].."
  exit 2
fi

invoice_type="$1"
shift
invoice_prefix="$1"
shift

csvdata(){
  local i
  for i in "$@"; do
    echo -En '"'$i'";'
  done
}

idstofilter() {
  local name i
  name=$1
  shift
  for i in $*; do
    echo -n "$name=$i "
  done
}

invoices=$(pslist order_invoices "$@")
eval $(pslist -Fenvarr order_invoices "$@" date_add number invoice_address total_paid_tax_incl id_order number delivery_address)

eval $(pslist -Fenvarr --or order_details $(idstofilter id_order $invoices) product_name product_reference total_price_tax_incl)

for i in $invoices; do
    id="${invoice_prefix}"$(printf %06d ${order_invoices[$i,id_order]})
    invoice_address=$(echo ${order_invoices[$i,invoice_address]} | sed -e 's#<br />#,#' -e 's#<br />#,#' -e 's#<br />#,#' | cut -d ',' -f 2,3)
    customer=$(echo ${order_invoices[$i,invoice_address]} | sed -e 's#<br />#,#' -e 's#<br />#,#' -e 's#<br />#,#' | cut -d ',' -f 1)
    delivery_address=$(echo ${order_invoices[$i,delivery_address]} | sed -e 's#<br />#,#' -e 's#<br />#,#' -e 's#<br />#,#' | cut -d ',' -f 2,3)
    for invd in $(pslist order_details id_order=${order_invoices[$i,id_order]}); do
    #         1  			2                       3				4			5		6			7			
      csvdata "$invoice_type" 		$id 	${order_invoices[$i,number]}	"${order_invoices[$i,date_add]}"	"$customer"	"$invoice_address"	"$delivery_address"
    #         8							9					10
      csvdata "${order_details[$invd,product_reference]}"	"${order_details[$invd,product_name]}"	"${order_details[$invd,total_price_tax_incl]}"
      echo	
    done
done
