#!/bin/bash
set -x

# Please use output file from command: nmap -O --osscan-guess 137.15.210.0/24
if [ $# -lt 1 ]; then
  echo "Usage: $0 <output file>"
  exit $LINENO
fi

nampOutput=$1
jsonFile=${nameOutput%.*}.json
if [ -f "$jsonFile" ]; then
  rm -f $jsonFile
fi

echo "Scanning Network Started ..."
#nmap -O --osscan-guess 137.15.210.0/24 > $nampOutput
echo "Scanning Network Finished ..."

if [ ! -f $nampOutput ]; then
  echo "TOOLS-E-FILE, File $nampOutput not found."
  exit $LININO
fi
devRE="^Nmap scan report for *"
hiRE="^Nmap scan report for (.*) [(]{1}([0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3})[)]{1}$"
ipRE="^Nmap scan report for[ \t]{1}([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$"
notShownRE="^Not shown: *"
hostStatusRE="Host is (.*) [(]{1}.*$"
portsHeadRE="^PORT.*STATE.*SERVICE"
portRE="^([0-9]{1,5})/([a-zA-Z]{1,3}) {1,4}(.*) {1,6}(.*)$"
macRE="MAC Address: ([A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}) [(]{1}(.*)[)]{1}"
devTypeRE="^Device type: (.*)"
osRE="^Runnin.*: (.*)$"
osCPERE="^OS CPE:*" 
osDetailsRE="^OS details:*"
osGuessRE="^Aggressive OS guesses:.*(Sun Solaris [0-9]+.[0-9]+).*$"
os2ndGuessRE="Aggressive OS guesses: (.*) or *$"

netDRE="Network Distance: ([0-9]+) hop"

while read line
do
  if [[ $line =~ "^$" ]]; then
    continue
  fi
  if [[ $line =~ $devRE ]]; then
    # There is host name available.
    if [[ $line =~ $hiRE ]]; then
      hName=${BASH_REMATCH[1]}
      ipAddress=${BASH_REMATCH[2]}
    fi
    # There is no host name available.
    if [[ $line =~ $ipRE ]]; then
      hName=""
      ipAddress=${BASH_REMATCH[1]}
    fi
    if [ ! -z $ipAddress ]; then
        index=0
        for i in ${ipAddress//./ }
        do
          echo $i
          index=$((index*1000+$i))
        done
    fi

    read line
  fi

  # Process hostStatus
  if [[ $line =~ $hostStatusRE ]]; then
    devStatus=${BASH_REMATCH[1]}
    read line
  fi
  
  # Process Not Shown
  if [[ $line =~ $notShownRE ]]; then
    read line
  fi
  
  # Process Port Header
  portsDoc=""
  if [[ $line =~ $portsHeadRE ]]; then
    read line
    while [[ $line =~ $portRE ]]
    do
      netSvcName=""
      portNumber=${BASH_REMATCH[1]}
      protocol=${BASH_REMATCH[2]}
      portStatus=${BASH_REMATCH[3]}
      netSvcName=${BASH_REMATCH[4]}
      if [ -z "$portsDoc" ]; then
        portsDoc="{portnumber:\"$portNumber\", protocol:\"$protocol\", portstatus:\"$portStatus\", netservice:\"$netSvcName\"}"
      else
        portsDoc="$portsDoc, {portnumber:\"$portNumber\", protocol:\"$protocol\", portstatus:\"$portStatus\", netservice:\"$netSvcName\"}"
      fi

      case $netSvcName in
        printer)
          devType=$netSvcName
          ;;
        oracle)
          devType="Oracle Database Server"
          ;;
        domain)
          devType="DNS"
          ;;
      esac

      read line
    done
  fi

  # Process MAC Address
  if [[ $line =~ $macRE ]]; then
    macAddress=${BASH_REMATCH[1]}
    netCardMaker=${BASH_REMATCH[2]}
    netCardDoc="{macaddress: \"$macAddress\", netcardmaker:\"$netCardMaker\"}"
    read line
  fi

  # Process device type
  if [[ $line =~ $devTypeRE ]]; then
    if [ -z "$devType" ]; then
      devType=${BASH_REMATCH[1]}
    fi
    read line
  fi

  # Process running os
  if [[ $line =~ $osRE ]]; then
    os=${BASH_REMATCH[1]}
    solaris10RE="Running: Sun Solaris 9.*10*"
    if [[ $line =~ $solaris10RE ]]; then
      os="Sun Solaris 10"
    fi
    read line
  fi

  # Process OS CPE
  if [[ $line =~ $osCPERE ]]; then
    read line
  fi

  # Process OS Details
  if [[ $line =~ $osDetailsRE ]]; then
    solarisRE="*Oracle Solaris 11*"
    if [[ $line =~ $solarisRE ]]; then
      os="Oracle Solaris 11"
    fi

    solarisRE="*Sun Solaris 9 or 10*"
    if [[ $line =~ $solarisRE ]]; then
      os="Sun Solaris 10"
    fi

    lexmarkRE=".*(Lexmark .*)$"
    if [[ $line =~ $lexmarkRE ]]; then
      os=${BASH_REMATCH[1]}
    fi

    xeroxRE=".*(Xerox Phaser .*)$"
    if [[ $line =~ $xeroxRE ]]; then
      os=${BASH_REMATCH[1]}
    fi
    read line
  fi

  if [ -z "$os" ]; then
    if [[ $line =~ $osGuessRE ]]; then
      os=${BASH_REMATCH[1]}
      if [ -z "$os" ]; then
        if [[ $line =~ $os2ndGuessRE ]]; then
          os=${BASH_REMATCH[1]}
        fi
      fi
    fi
  fi
  # Process network distance
  if [[ $line =~ $netDRE ]]; then
    netDistance=${BASH_REMATCH[1]}
    read line
  fi
  
  if [ ! -z "$ipAddress" ]; then
    echo "db.devices.update({ipaddress:\"$ipAddress\"},{ \$set: { dindex: \"$index\",ipaddress: \"$ipAddress\",hostname:\"$hName\", devstatus:\"$devStatus\", ports:[$portsDoc], netcard:[$netCardDoc], devicetype: \"$devType\",os: \"$os\",networkdistance: \"$netDistance\",createdtime: \"`date '+%F %T'`\"}}, {upsert: true})" >> ${nampOutput}.json
  fi
  hName=""
  ipAddress=""
  portsDoc=""
  netCardDoc=""
  devType=""
  os=""
  netDistance=""
done < $nampOutput
