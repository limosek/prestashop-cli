#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will translate given object and properties
## 
## Arguments are filter(s) to match orders
###########################################################

if [ -z "$4" ]; then
  echo $0 "objects property[,prperty]... from to trans_function filter"
  exit 2
fi

objects2object()
{
    psobjects | grep ^"$1 " | tr -s ' ' | cut -d ' ' -f 2
}

translate_yandex()
{
   echo $* | trans -b -e yandex -s $from -t $to
}

translate_google()
{
   echo $* | trans -b -e google -s $from -t $to
}

objects=$1
object=$(objects2object $1)
shift
properties=$(echo $1 | tr ',' ' ')
shift
from=$1
shift
to=$1
shift
trans_function=$1
shift

lfrom=$(pslist languages iso_code=$from)
lto=$(pslist languages iso_code=$to)

objs=$(pslist $objects "$@")
if [ -z "$lfrom" ]; then
  echo "Language $from not enabled?"
  exit 2
fi
if [ -z "$lto" ]; then
  echo "Language $to not enabled?"
  exit 2
fi

echo $@

for o in $objs; do
    for p in $properties; do
      ftxt=$(psget --language=$lfrom --noescape $object $o $p)
      ttxt=$($trans_function $ftxt)
      echo "$ftxt ==>> $ttxt" >&2
      echo psupdate --base64 --language=$lto $object $o $p=$(echo $ttxt | base64 -w0)
    done
done
