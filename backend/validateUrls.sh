#!/bin/bash
 set -x
source ~/.bash_profile
workSpace=`dirname $0`
[[ $workSpace == "." ]] && workSpace=`pwd`

. $workSpace/config.sh

jsonOutput=/var/tmp/appurls.txt

logFolder=$workSpace/logs

[[ ! -d "$logFolder" ]] && mkdir $logFolder

urlStatusFile=$logFolder/urlStatus_report.js

if [ -f $urlStatusFile ]; then
  rm -f $urlStatusFile
fi
urlRexp="https{0,1}://.{1}"
okStatus="HTTP request sent, awaiting response... 200 OK"
badStatus="Connection refused."
badPort="Bad port number."
notFound="404 Not Found"

cmdOptions=" --ssl --sslCAFile=${mongoDBServerCert} --sslPEMKeyFile=${mongoDBClientCert} --sslAllowInvalidHostnames "
mongoexport $cmdOptions -h 127.0.0.1 -d tcsit -c latestWASStatus -q '{ appurl: { $regex: /^https/i }}' -f '_id,appurl'  -o $jsonOutput

documents=`cat $jsonOutput`

for doc in $documents
do
  docId=`echo $doc| cut -d\" -f6`
  url=`echo $doc| cut -d\" -f10`
  ##urlStatus=`$workSpace/chkCSISHttp.sh $url`

  if [[ ! $url =~ $urlRexp ]]; then
    echo "%%TCS-E-PARAMETER, target url must start with https://"
    exit $LINENO
  fi

  pid=$$

  tmpFile=/tmp/${pid}.log
  statusFile=/var/tmp/checkurl.html

  (cd /tmp; wget --no-check-certificate -o $statusFile $url >${tmpFile} 2>&1)

  grep "$okStatus" ${statusFile}
  checkStatus=$?

  if [ $checkStatus -ne 0 ]; then

    for i in "$badStatus" "$notFound" "$badPort"
    do
      echo "^^^^^^^^^^^^^^^^ $i ^^^^^^^^^^^^^^^^^^^^^^^^^^"
      message=`grep "$i" ${statusFile}`
      if [ ! -z "$message" ]; then
        urlStatus="$i"
      fi
    done
  else
    urlStatus="OK"
  fi
  rm -f ${statusFile} ${tmpFile}
  echo $urlStatus
  echo "db.latestWASStatus.update({_id: ObjectId(\"${docId}\") },{ \$set: { appurlvalidity: \"$urlStatus\"}}, {upsert: false})" >> $urlStatusFile
  ###echo "db.latestWASStatus.update({_id: ObjectId(\"${docId}\") },{ \$set: { appurlvalidity: \"$urlStatus\", timestamp: \"`date '+%F %T'`\"}}, {upsert: false})" >> $urlStatusFile
  scp $urlStatusFile ${transUser}@${transSrv}:${transRepo}
  urlStatus=""
done
