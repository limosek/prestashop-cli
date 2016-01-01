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

invoices=$(pslist order_invoices "$@")
eval $(pslist -Fenvarr order_invoices "$@" date_add number invoice_address total_paid_tax_incl id_order number invoice_address)

for i in $invoices; do
    id="${invoice_prefix}"$(printf %06d ${order_invoices[$i,id_order]})
    invoice_address=$(echo ${order_invoices[$i,invoice_address]} | sed -e 's#<br />#,#' -e 's#<br />#,#' -e 's#<br />#,#' | cut -d ',' -f 2,3)
    customer=$(echo ${order_invoices[$i,invoice_address]} | sed -e 's#<br />#,#' -e 's#<br />#,#' -e 's#<br />#,#' | cut -d ',' -f 1)
    #         1  			2                       3				4					5		6			7
    csvdata "$invoice_type" 		$id 	${order_invoices[$i,number]}	"${order_invoices[$i,date_add]}"	"$customer"	"$invoice_address" 	"${order_invoices[$i,total_paid_tax_incl]}"
    echo	
done
