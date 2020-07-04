#!/bin/bash
# set -x

workSpace=`dirname $0`

[ "$workSpace" == "." ] && [ workSpace=`pwd` ]

srvList="toChangePasswd.lst"

if [ ! -f "$srvList" ]; then
  echo "%%SECURITY-E-FILE, cannot find file $srvList"
  exit $LINENO
fi

[ ! -d "logs" ] && [ mkdir -p $workSpace/logs ]

user=root
oldPwd="csis2006"
newPwd="suK3YuTe"

servers=`cat "$srvList"`
expectCMD="./sshchpwd.exp"

for srv in $servers
do
  ###srvStatus=`ping -c 3 $srv > /dev/null`
  ping -c 3 $srv > /dev/null
  srvStatus=$?
  case $srvStatus in
    0)
      echo "%%SECURITY-I-PROGRESS, Changing $user password."
      $expectCMD $srv $user "$oldPwd" "$newPwd"
      ;;
    1)
      echo "$srv is not reachable."
      continue
      ;;
    2)
      echo "$srv doesn't exist."
      continue
      ;;
    *)
      echo "Code: $srvStatus Returned."
      ;;
  esac
done
