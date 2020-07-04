#!/bin/bash
# set -x

workSpace=`dirname $0`
[ $workSpace == "." ] && [ workSpace=`pwd` ]

configDir=`dirname $workSpace`
source /root/.bash_profile

source ${configDir}/config.sh
instanceList="$workSpace/oraInstances.lst"
tnspingCmd=/usr2/app/oracle/product/11.2.0.3/bin/tnsping
sqlplusCmd=/usr2/app/oracle/product/11.2.0.3/bin/sqlplus

connStringRegex="Attempting to contact.*HOST = (.*)[.]{1}csd.*[)]{1}[(]{1}PORT = ([0-9]{4,}).*)$"
###resultFile="/var/tmp/oraInstanceStatus_Status.json"
updateFile="/var/tmp/latestOraStatus_Status.js"

###if [ -f "$resultFile" ]; then
   ###rm -f $resultFile 
###fi
if [ -f "$updateFile" ]; then
   rm -f $updateFile
fi
###touch $resultFile
touch $updateFile
priority=9
instances=`cat $instanceList`

for instance in $instances
do
  # Get Instance Information
  connString=`$tnspingCmd $instance|grep ^Attempting`
  if [[ $connString =~ $connStringRegex ]]; then
      iHost=${BASH_REMATCH[1]}
      iPort=${BASH_REMATCH[2]}
  fi
  
  if [ $instance == "V9PROD" ]; then
      priority=0
  fi

  if [ $instance == "PFRPROD" ]; then
      priority=1
  fi

  if [ $instance == "CSISRPPD" ]; then
      priority=2
  fi

  tStamp=`date "+%F %T"`
  status=`$workSpace/check_oracle_instance $instance`
  ###echo "{timestamp: \"$tStamp\",instance: \"$instance\",host: \"$iHost\", port: \"$iPort\", status: \"$status\"}" >> $resultFile
  echo "db.latestOraStatus.update({instance: \"$instance\"},{ \$set: {timestamp: \"$tStamp\",instance: \"$instance\",host: \"$iHost\", port: \"$iPort\", status: \"$status\", priority: $priority}}, {upsert: true})" >> $updateFile
  connString=""
done

if [ -f $updateFile ]; then
    ###scp $resultFile  ${transUser}@${transSrv}:${transRepo}
    scp $updateFile  ${transUser}@${transSrv}:${transRepo}
fi
