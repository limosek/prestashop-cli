#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export orders to import as invoices
## 
## Arguments are filter(s) to match orders
###########################################################

if [ -z "$2" ]; then
  echo $0 "invoice_type payment [filter].."
  exit 2
fi

invoice_type="$1"
shift
payment="$1"
shift

csvdata(){
  local i
  for i in "$@"; do
    echo -En '"'$i'";'
  done
}

orders=$(pslist orders payment="$payment" "$@")
eval $(pslist -Fenvarr orders "$@" date_add invoice_number invoice_date reference id_customer id_address_invoice id_address_delivery payment total_products total_shipping total_discounts)

for o in $orders; do
  eval $(pslist -Fenvarr order_details id_order=$o)
done

for o in $orders; do
    eval $(psget -Fenv customer ${orders[$o,id_customer]})
    cid=$id_customer
    daddress=${orders[$o,id_address_delivery]}
    iaddress=${orders[$o,id_address_invoice]}
    eval $(psget -Fenv address $iaddress)
    eval $(psget -Fenv address $daddress)
    [ "${orders[$o,invoice_number]}" = 0 ] && continue
    #         1  2                          	3				4				5
    csvdata "$o" ${orders[$o,invoice_number]} 	"${orders[$o,reference]}" 	"${orders[$o,date_add]}"	"$lastname"
    #		6		7		8	9		10		11		12
    csvdata "$firstname" 	"$address1"	"$city"	"$postcode"	"$phone"	"$phone_mobile"	"$email"
    #		13				14				15 				16			17
    csvdata "${orders[$o,total_products]}"	${orders[$o,total_shipping]}	${orders[$o,total_discounts]}	"${orders[$o,payment]}"	"$invoice_type"
    echo
done
