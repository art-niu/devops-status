#!/bin/bash
 set -x

if [ $# -lt 1 ]; then
  echo "Usage: $0 <Source File>"
  exit $LINENO
fi

srcFile=$1
collectionName=sys_httpresponsecode
primaryIndex=""
createAt=`date "+%Y-%m-%d %T "`
hostName=`hostname -s`
userId=`whoami`
pid=$$

tmpJSONFile="/var/tmp/json_${hostName}_${pid}.js"

if [ -f $tmpJSONFile ]; then
  rm -f $tmpJSONFile
fi


while read line
do
  httpCode=`echo $line |cut -d" " -f1`
  description=`echo $line |cut -d" " -f2-`
  primaryIndex=$httpCode

  docString="{ \"collectionname\": \"$collectionName\", \"primaryindex\":\"$primaryIndex\",  \"httpcode\":\"$httpCode\", \"description\":\"$description\",\"createat\":\"${createAt}\",\"updatefrom\":\"$hostName\",\"updatedby\":\"${userId}\"} "

  echo "$docString" > $tmpJSONFile
  curl -k -d "@$tmpJSONFile" -X POST https://dynamics.csd.toronto.ca/crud/updateCollectionJson.php
  curl -k -d "@$tmpJSONFile" -X POST https://github.csd.toronto.ca/crud/updateCollectionJson.php
  rm -f $tmpJSONFile
 
done < $srcFile


