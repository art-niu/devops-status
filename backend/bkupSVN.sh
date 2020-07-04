#!/bin/bash

# This script must be placed on Jenkins Server
transUser=dboard
transSrv=dynamics.csd.toronto.ca
transRepo=/usr1/dboardTransition


startTime=`date +%s`
startAt=`date '+%F %T'`

svnRep="/srv/svn"
bkupRep="/bkup"

reps=`ls $svnRep`
numberOfReps=`ls $svnRep|wc -l`

toDay=`date +%a`
if [ -d "${bkupRep}/svn_${toDay}" ]; then
  echo "Cleanning up ${bkupRep}/svn_${toDay}"
  #echo "rm -rf ${bkupRep}/svn_${toDay}"
  rm -rf "${bkupRep}/svn_${toDay}"
fi

succeeded=""
failed=""
dstPath="${bkupRep}/svn_${toDay}"
mkdir -p $dstPath
for rep in $reps
do
  echo "$rep - Started at `date '+%F %T'`"
  echo "Backing up /srv/svn/$rep to $bkupRep/svn_${toDay}/$rep"
  svnadmin hotcopy ${svnRep}/$rep ${bkupRep}/svn_${toDay}/$rep
  if [ $? -eq 0 ]; then
    succeeded="${succeeded},$rep"
  else
    failed="${failed},$rep"
  fi
  echo "$rep - Ended at `date '+%F %T'`"
done
succeeded=${succeeded#*,}
failed=${failed#*,}
finishAt=`date '+%F %T'`
endTime=`date +%s`
timeElappsed=`expr $endTime - $startTime`

bkupName="svn"
serverName=`hostname`


updateString="db.backups.update({bkupname:\"$bkupName\", server :\"${serverName}\", dayofbkup:\"${toDay}\" },{ \$set: { bkupname:\"$bkupName\",startat:\"${startAt}\",finishat:\"${finishAt}\",server:\"${serverName}\",succeeded:\"${succeeded}\", failed:\"${failed}\",numberofreps:\"${numberOfReps}\", elapsedtime:${timeElappsed}, sourcepath: \"$svnRep\", bkuppath: \"$dstPath\"}}, {upsert: true})\n"

svnBkupStatus="/var/tmp/svn_bkup_${bkupName}_${serverName}_${toDay}.js"
echo "$updateString" > $svnBkupStatus

scp "$svnBkupStatus" ${transUser}@${transSrv}:${transRepo}

if [ $? -eq 0 ]; then
  echo "Clenning up $svnBkupStatus"
  rm -f $svnBkupStatus
fi

