#!/bin/bash
# set -x

# This script is to kill user database session , it must be placed in <target server>:/usr/local/bin

if [ $# -lt 1 ]; then
  echo "Usage: $0 <process ID>"
  exit $LINENO
fi

echo "Running as `id`"
processID=$1

userName="ora10ias"

userSessionREx="^$userName[ ]{+}$processID$"
userSessionREx="$userName*$processID"

srchResult=`ps -o user= -o pid= -p $processID`

if [ ! -z "$srchResult" ]; then
    dUserName=${srchResult%% *}
    pID=${srchResult##* }
    if [ "$dUserName" == "$userName" ] && [ "$pID" == "$processID" ]; then
      kill -9 $processID
      if [ $? -eq 0 ]; then
        echo 0
      else
        echo "FAILED"
        exit 1
      fi
    else
      echo "NOTIASSESSION"
      exit 2
    fi
else
  echo "NOTEXIST"
  exit 3
fi
