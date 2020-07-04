#!/bin/bash
# This script to is to verify csis url
# This file must be copied to /usr/lib/nagios/plugins
set -x
if [ $# -lt 1 ]; then
  echo "%%TCS-E-PARAMETER, url is not defined."
  echo "Usage: $0 <url list file>"
  exit $LINENO
fi

tgtUrlList=$1
#urlDescription="${*:2}"

startAt=`date "+%Y-%m-%d %T "`
epochTimeStamp=`date +%s`

collectionName="urlStatus"
hostName=`hostname -s`
userId=`whoami`
finishAt=`date "+%Y-%m-%d %T "`
timeElappsed=0;
priority=10
urlRexp="https{0,1}://.{1}"

pid=$$

function checkURL {

tgtUrl=$1
primaryIndex="${tgtUrl}"
urlDescription="${*:2}"
if [[ ! $tgtUrl =~ $urlRexp ]]; then
  echo "%%TCS-E-PARAMETER, target url must start with https://"
  exit $LINENO
fi

httpCode=`curl -k -o /dev/null --silent --head --write-out '%{http_code}\n' $tgtUrl`

resultString="{ \"epochstartat\": $epochTimeStamp,  \"collectionname\": \"$collectionName\",  \"primaryindex\":\"$primaryIndex\",\"url\":\"$tgtUrl\", \"startat\":\"${startAt}\",\"finishat\": \"${finishAt}\",\"updatefrom\":\"$hostName\",\"updatedby\":\"${userId}\",\"urldescription\":\"${urlDescription}\",\"status\":${httpCode},\"elapsedtime\":${timeElappsed}, \"priority\":  $priority }"

statusJSONFile="/var/tmp/json_status_${hostName}_${pid}.js"
echo "$resultString" > $statusJSONFile

curl -k -d "@$statusJSONFile" -X POST https://github.csd.toronto.ca/crud/updateCollectionJson.php
curl -k -d "@$statusJSONFile" -X POST https://dynamics.csd.toronto.ca/crud/updateCollectionJson.php

rm -f $statusJSONFile
}

okStatus="HTTP request sent, awaiting response... 200 OK"
badStatus="failed: Connection refused."
badPort="Bad port number."
notFound="HTTP request sent, awaiting response... 404 Not Found"

while read line
do
  url=`echo $line |cut -d' ' -f1`
  description=`echo $line |cut -d' ' -f2-`
  checkURL $url "$description"
done < $tgtUrlList

exit 0
