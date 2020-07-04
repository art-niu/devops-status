#!/bin/bash
#set -x

workSpace=`dirname $0`

[ $workSpace == "." ] && [ workSpace = `pwd` ]

. $workSpace/config.sh

wsroot="/opt/IBM/WebSphere/AppServer/profiles"
profiles="`cd $wsroot;ls -d *Dmgr*`"

echo $profiles
notificationList="aniu@toronto.ca"
###notificationList="aniu@toronto.ca ahon@toronto.ca arthur.niu@gmail.com"

os=`uname`
case $os in
  SunOS)
    mailCmd="mailx"
    ;;
  Linux)
    mailCmd="mailx -S smtp=mail.toronto.ca "
    ;;
  *)
    echo "$os is not supported."
    exit $LINENO
    ;;
esac

cd $workSpace
for p in $profiles
do
  wasroot="/opt/IBM/WebSphere/AppServer/profiles/$p"
  wasbin="$wasroot/bin"
  wsadminCMD="$wasbin/wsadmin.sh -lang jython -f $workSpace/jvmStatus.py $p"
  eval $wsadminCMD

status=/var/tmp/wasAppStatus_${p}.json
lStatus=/var/tmp/latestWASStatus_${p}.js

if [ -f "$status" ]; then
  hostName=`hostname`
  subject="$p Status on $hostName"
  recipient=${notificationList%% *}
  ccList=${notificationList#* }
###  $mailCmd -s "$subject" "$notificationList" < $status
  scp "$status" ${transUser}@${transSrv}:${transRepo}
fi

if [ -f "$lStatus" ]; then
  scp "$lStatus" ${transUser}@${transSrv}:${transRepo}
  mv "$lStatus" ${lStatus}.`date +%s`
fi 

done

