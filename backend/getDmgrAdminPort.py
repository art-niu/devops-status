#import java
#lineSeparator = java.lang.System.getProperty('line.separator')

def getDmgrConsoelPort ():
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
                host = AdminConfig.showAttribute(endPoint, "host" )
                port = AdminConfig.showAttribute(endPoint, "port" )
                return port
                #print "System information: Endpoint Name  : " +  endPointName + " Host : " + host + " port : " + port 
            #endif
      #endfor
  #endfor
#def
