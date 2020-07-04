#!/bin/bash
#set -x

workSpace=`dirname $0`

if [ $# -lt 1 ]; then
  echo "Usage: $0 <8|9>"
  exit $LINENO
fi

version=$1
[ $workSpace == "." ] && [ workSpace = `pwd` ]

. $workSpace/config.sh

if [ $version == 8 ]; then
  wsroot="/opt/IBM/WebSphere/AppServer/profiles"
else
  wsroot="/opt/IBM/WebSphere/AppServer_9/profiles"

fi
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
  wasroot="$wsroot/$p"
  wasbin="$wasroot/bin"
  wsadminCMD="$wasbin/wsadmin.sh -lang jython -f $workSpace/getports.py $p"
  eval $wsadminCMD

done

