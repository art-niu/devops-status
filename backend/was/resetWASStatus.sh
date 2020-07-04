#!/bin/bash
# set -x
source ~/.bash_profile
workSpace=`dirname $0`
[[ $workSpace=="." ]] && workSpace=`pwd`

jsonOutput=/var/tmp/appurls.txt

logFolder=$workSpace/logs

[[ ! -d "$logFolder" ]] && mkdir $logFolder

resetStatus=$logFolder/reset_WASStatus.js

if [ -f $resetStatus ]; then
  rm -f $resetStatus
fi


echo "db.latestWASStatus.update({},{ \$set: { status: \"-\",timestamp: \"`date '+%F %T'`\"}}, {upsert: false,multi:true});"  >$resetStatus

