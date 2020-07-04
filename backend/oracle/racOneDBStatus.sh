#!/bin/bash
set -x
# The script is to check Oracle DB instance

export ORACLE_HOME=/usr2/app/oracle/product/11.2.0.3
export PATH=$PATH:$ORACLE_HOME/bin
export ORACLE_BASE=/usr2/app/oracle
export LD_LIBRARY_PATH=$ORACLE_HOME/lib

workSpace=`dirname $0`

if [ "$workSpace" == "." ]; then
  workSpace=`pwd`
fi

parentFolder=`dirname $workSpace`

source $parentFolder/config.sh

logsFolder=$workSpace/logs
mkdir -p $logsFolder

tmpFile=$logsFolder/dbStatus.log

if [ -f "$tmpFile" ]; then
  rm -f $tmpFile
fi

touch $tmpFile

updateFile=/var/tmp/racDBStatus_report.js

if [ -f "$updateFile" ]; then
  rm -rf $updateFile
fi 

regRunning="^Instance[ \t]{1}(.*)[ \t]{1}is running on node (.*)$"
cmdLine="/usr2/app/oracle/product/11.2.0.3/bin/srvctl status database -d "

dbs=`cat $workSpace/racOneDB.lst`

for db in $dbs
do
  $cmdLine $db > $tmpFile
  stLine=`head -1 $tmpFile`
  if [[ $stLine =~ $regRunning ]]; then
      iName=${BASH_REMATCH[1]}
      iNode=${BASH_REMATCH[2]}

      status="Up"
  else
      iName="-"
      iNode="-"
      status="-"
  fi
  echo "db.latestRAC1Status.update({dbname: \"$db\"},{ \$set: {timestamp: \"`date "+%F %T"`\",dbname: \"$db\",node: \"$iNode\", instance: \"$iName\", status: \"$status\"}}, {upsert: true})" >> $updateFile
done

if [ -f $resultFile ]; then
    scp $updateFile ${transUser}@${transSrv}:${transRepo}
fi

exit 0

# PRCD-1120 : The resource for database tcs could not be found.
# PRCR-1001 : Resource ora.tcs.db does not exist

# Instance TCSPROD_1 is running on node sun08
