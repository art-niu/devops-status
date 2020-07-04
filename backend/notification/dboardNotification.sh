#!/bin/bash
# set -x

export EMAIL="Morning Updates <aniu@toronto.ca>"
source ~/.bash_profile
notificationList="aniu@toronto.ca arthur.niu@gmail.com ahon@toronto.ca"
statusFile=/var/tmp/dbstatus.html
notifications="https://dboard.csd.toronto.ca/crud/morningUpdateWasDeploymentStatus.php https://dboard.csd.toronto.ca/morningUpdates.php"
cd /var/tmp

for n in ${notifications}
do
  statusFile="${n##*/}.html"
  subject="TCS Dash Board - ${statusFile%%.*} `date +%A`"
  wget --no-check-certificate $n -O $statusFile

  /bin/mutt -e "set content_type=text/html" -e 'my_hdr From: Morning Updates <aniu@toronto.ca>' $notificationList -s "$subject" < $statusFile
done
