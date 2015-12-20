#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export customers and addresses to csv
## Fist argument is number which is added to all customerid. Can be 0.
## Rest of arguments are standard pslist filters
## added to ids of customer to avoid conflicts.
###########################################################

if [ -z "$1" ]; then
   echo "$0 idshift [filter1]...[filtern]"
   exit 1
fi

idshift=$1
shift

customers=$(pslist customers "$@")

for c in $customers; do
    eval $(psget -Fenv customer $c)
    cid=$(expr $c + $idshift)
    address=$(pslist addresses id_customer=$id | head -1)
    if [ -n "$address" ]; then
      eval $(psget -Fenv address $address)
      echo "$cid;$lastname;$firstname;$address1;$city;$postcode;$phone;$phone_mobile;$email"
    fi
done