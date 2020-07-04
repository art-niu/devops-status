# Provide this example as a Jython script file and run it with the "-f" option:

# ------------------------------------------------------
# get line separator
import sys
dmgrProfile =  sys.argv[0]
ScriptLocation = java.lang.System.getProperty('script.dir')
if (ScriptLocation==None or ScriptLocation==""):
        ScriptLocation = java.lang.System.getProperty('user.dir')

envOfProfile=dmgrProfile[0:5]
priority='5'

if envOfProfile == "an9Pr":
  priority='0'
if envOfProfile=="wsPr":
  priority='1'
if envOfProfile=="secur":
  priority='2'
if (envOfProfile=="ws9QA"):
  priority='3'
if (envOfProfile=="an9DV"):
  priority='8'
if (envOfProfile=="ws9QA"):
  priority='9'

import java.lang.System  as  sys
import os
import string
from java.util import Date
from java.text import SimpleDateFormat
import socket

if socket.gethostname().find('.')>=0:
    hname=socket.gethostname()
else:
    hname=socket.gethostbyaddr(socket.gethostname())[0]

def getAppStatus(appName):
    # If objectName is blank, then the application is not running.
    objectName = AdminControl.completeObjectName('type=Application,name=' + appName + ',*')
    if objectName == "":
        appStatus = 'Stopped'
    else:
        appStatus = 'Running'
    return appStatus

lineSeparator = sys.getProperty('line.separator')
cells = AdminConfig.list('Cell').split('\n')

execfile(ScriptLocation+"/getAdminConsoleUrl.py" )

#tstamp = datetime.datetime.now().time()
tstamp = SimpleDateFormat("yyyy-MM-dd HH:mm:ss").format(Date()) 

print "ScriptLocation: " + ScriptLocation
adminConsole=getDmgrAdminConsoel()
for cell in cells:
    ###nodes = AdminConfig.list('Node', cell).split('\n')
    nodes = AdminTask.listManagedNodes().split('\n')
    for node in nodes:
        #--------------------------------------------------------------
        # lines 19-23 find all the running servers belonging to the cell
        # and node, and process them one at a time
        #--------------------------------------------------------------
        cname = AdminConfig.showAttribute(cell, 'name')
        print node
        ###nname = AdminConfig.showAttribute(node, 'name')
        nname = node
        #servs = AdminControl.queryNames('type=Server,cell=' + cname + ',node=' + nname + ',*').split('\n')
        servs = AdminTask.listServers('[-serverType APPLICATION_SERVER ]').split('\n')
        jFileName='/var/tmp/wasAppStatus_' + dmgrProfile + '.json'
        uJFileName='/var/tmp/latestWASStatus_' + dmgrProfile + '.js'
        if os.path.exists(jFileName):
            os.remove(jFileName)
        if os.path.exists(uJFileName):
            os.remove(uJFileName)

        ###jsonFile=open(jFileName,'w')
        uJsonFile=open(uJFileName,'w')

        print "Servers: " + str(servs)
        print "Number of running servers on node " + nname + ": %s \n" %(len(servs))
        serverString = "Number of running servers on node " + nname + ": %s \n" %(len(servs))
        #jsonFile.write(serverString)
        for server in servs:
            #---------------------------------------------------------
            #lines 28-34 get some attributes from the server to display;
            # invoke an operation on the server JVM to display a property.
            #---------------------------------------------------------
            sname = AdminConfig.showAttribute(server, 'name')
            server = AdminControl.completeObjectName('type=Server,name=' + sname + ',*')

            # If the JVM is down.
            if server is None or server == "" :
            	print "JVM Name: " + str(sname)
            	serverStatus = 'Stopped'
            	apps = AdminApp.list("WebSphere:cell=" + cname + ",node=" + nname + ",server=" + sname).splitlines()
            	if apps is None :
            		print "No applications installed on " + str(server)
            		continue

            	appStatus = 'Stopped'
            	print "Applications: " + str(apps) + " on: " + str(sname)
            	for aname in apps:
              		print "Application: " + str(aname)
            		if aname not in ["ibmasyncrsp"] :
                  		updateString = 'db.latestWASStatus.update({cell:"' + cname + '", node:"' + nname + '", server :"' + sname  + '", appname:"' + aname + '" },{ $set: { adminconsole: "' + adminConsole + '",cell:"' + cname + '", node:"' + nname + '", server :"' + sname + '", serverstatus :"' + serverStatus + '", appname:"' + aname + '", status: "' + appStatus + '",timestamp: "' + tstamp + '",priority: ' + priority + '}}, {upsert: true})\n'
                  		uJsonFile.write(updateString)
                  		print updateString
                  		print "======================================================================================================================================="
            	continue

            print "JVM Name: " + str(server) + ": " + str(sname)
            ptype = AdminControl.getAttribute(server, 'processType')
            pid   = AdminControl.getAttribute(server, 'pid')
            state = AdminControl.getAttribute(server, 'state')
            #jvm = AdminControl.queryNames('type=JVM,cell=' + cname + ',node=' + nname + ',process=' + sname + ',*')
            jvm = 'type=JVM,cell=' + cname + ',node=' + nname + ',process=' + sname + ',*'
            #osname = AdminControl.invoke(jvm, 'getProperty', 'os.name')
            osname = java.lang.System.getProperty( "os.name" )
            print " " + sname + " " +  ptype + " has pid " + pid + "; state: " + state + "; on " + osname + "\n"
 
            # DeploymentManager
            if ptype not in ["DeploymentManager", "NodeAgent"] :
              apps = AdminControl.queryNames('type=Application,cell=' + cname + ',node=' + nname + ',process=' + sname + ',*').splitlines()
              print "Number of applications running on " + sname + ": %s \n" % (len(apps) - 1)
              print "Application: " + str(apps)
              apps = AdminApp.list('WebSphere:cell=' + cname + ',node=' + nname + ',server=' + sname).splitlines()
              print "Application: " + str(apps)
              for aname in apps:
                #aname = AdminControl.getAttribute(app, 'name')
                if aname not in ["ibmasyncrsp"] :
                  appStatus = getAppStatus(aname)
                  ###appStatusString = '{timestamp: "' + tstamp + '", host: "' + hname + '", adminconsole: "' + adminConsole + '", cell: "' + cname + '", node: "' + nname + '", server: "' + sname + '", appname: "' + aname + '", status: "' + appStatus  + '", appurl: "", appurlvalidity: ""}\n'
                  ###jsonFile.write(appStatusString)
                  ###print appStatusString. Use below line for new collection
                  ###updateString = 'db.latestWASStatus.update({cell:"' + cname + '", node:"' + nname + '", server :"' + sname + '", appname:"' + aname + '" },{ $set: { adminconsole: "' + adminConsole + '",cell:"' + cname + '", node:"' + nname + '", server :"' + sname + '", appname:"' + aname +'", status: "' + appStatus + '",timestamp: "' + tstamp + '", appurl: "", appurlvalidity: ""}}, {upsert: true})\n'
                  ###Use below line for existing collection
                  updateString = 'db.latestWASStatus.update({cell:"' + cname + '", node:"' + nname + '", server :"' + sname + '", appname:"' + aname + '" },{ $set: { adminconsole: "' + adminConsole + '",cell:"' + cname + '", node:"' + nname + '", server :"' + sname + '", appname:"' + aname +'", status: "' + appStatus + '",timestamp: "' + tstamp + '",priority: ' + priority + '}}, {upsert: true})\n'
                  uJsonFile.write(updateString)
                  print updateString
                  print "======================================================================================================================================="
              #end for
        #end for
    #end for
#end for

###jsonFile.close()
uJsonFile.close()
