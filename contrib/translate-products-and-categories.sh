#!/bin/sh

PATH=$PATH:$(dirname $0)

if [ -z "$2" ]; then
  echo $0 "from_lang to_lang"
  echo "It will output commands to translate, not to translate at all"
  echo "If you want to translate, use $0 xx yy "'| sh -v'
  exit 2
fi

objects2object()
{
    psobjects | grep ^"$1 " | tr -s ' ' | cut -d ' ' -f 2
}

from=$1
shift
to=$1
shift

( 
  translate-objects.sh products description,description_short $from $to translate_yandex
  translate-objects.sh products name $from $to translate_google
  translate-objects.sh categories name $from $to translate_google 
  translate-objects.sh categories description $from $to translate_yandex 
)
