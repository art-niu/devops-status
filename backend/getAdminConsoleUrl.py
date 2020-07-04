#import java
#lineSeparator = java.lang.System.getProperty('line.separator')
import socket

if socket.gethostname().find('.')>=0:
    hname=socket.gethostname().split('.')[0]
else:
    hname=socket.gethostbyaddr(socket.gethostname())[0]

def getDmgrAdminConsoel ():
  servers = AdminConfig.list( 'ServerEntry' ).splitlines()
  for server in servers :
    ServerName = server.split( '(', 1 )[ 0 ]
    #print "System information: Server Name : " +  ServerName
    
    if ServerName == 'dmgr':
      NamedEndPoints = AdminConfig.list( "NamedEndPoint" , server).split(lineSeparator)
      for namedEndPoint in NamedEndPoints:
            endPointName = AdminConfig.showAttribute(namedEndPoint, "endPointName" )
            if endPointName == 'WC_adminhost_secure':
                endPoint = AdminConfig.showAttribute(namedEndPoint, "endPoint" )
                #host = AdminConfig.showAttribute(endPoint, "host" )
                port = AdminConfig.showAttribute(endPoint, "port" )
                url = "https://" + hname + ".csd.toronto.ca:" + port + "/ibm/console"
                return url
                #print "System information: Endpoint Name  : " +  endPointName + " Host : " + host + " port : " + port 
            #endif
      #endfor
  #endfor
#def
