#!/bin/bash
# set -x
#
# 
source /home/dboard/.bash_profile
statusRepo=/usr1/dboardTransition/

workSpace=`dirname $0`
[[ "$workSpace" == "." ]] && workSpace=`pwd`

. ${workSpace}/config.sh
database="tcsit"

###logFolder=$workSpace/logs

###[[ ! -d $logFolder ]] && mkdir $logFolder
###resetStatus=$logFolder/reset_WASStatus.js

###if [ -f $resetStatus ]; then
###  rm -f $resetStatus
###fi
cmdOptions=" --ssl --sslCAFile ${mongoDBServerCert} --sslPEMKeyFile ${mongoDBClientCert} "
statusReset=0
# Validate URLs
$workSpace/validateUrls.sh

#cd $statusRepo

statusFiles=`find $statusRepo -maxdepth 1 -type f`

for sf in $statusFiles
do
  bname=`basename $sf`
  catg=${bname%%_*}
  case $catg in
    wasAppStatus|oraInstanceStatus)
      mongoimport $cmdOptions --db $database --collection $sf
      rCode=$?
      if [ $rCode -ne 0 ]; then
        echo "%%DBOARD-E-IMPORT, failed to import $sf"
        echo "%%DBOARD-E-IMPORT, code: $rCode returned."
      else
        echo "%%DBOARD-I-IMPORT, successfully imported $sf"
        rm -f $sf
      fi
    ;;
    latestOraStatus|racDBStatus)
        mongo $cmdOptions --host 127.0.0.1 --port 27017 tcsit $sf
        rm -f $sf
    ;;
    wasDeploymentStatus|svn|elcqp)
        mongo $cmdOptions --host 127.0.0.1 --port 27017 tcsit $sf
        rm -f $sf
    ;;
    latestWASStatus|urlStatus)
        #if [ $statusReset == 0 ]; then
        #    # Reset status to -
        #    $workSpace/was/resetWASStatus.sh
        #    mongo $cmdOptions --host 127.0.0.1 --port 27017 tcsit $resetStatus
        #    statusReset=1
        #fi

        mongo $cmdOptions --host 127.0.0.1 --port 27017 tcsit $sf
        rm -f $sf
    ;;
    *)
      # Ignore files unknow catagory 
      echo "%%DBOARD-E-IMPORT, unknown catagory of file: $sf"
      ;;
  esac
done
