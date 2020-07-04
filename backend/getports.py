import java
lineSeparator = java.lang.System.getProperty('line.separator')

# get Nodes
#NodeIDs = AdminConfig.getid('/Cell:anDVCellManager01/Node:anDVCellManager01/')
NodeIDs = AdminConfig.getid('/Node:anDVCellManager01/')
arrayNodeIDs = NodeIDs.split(lineSeparator)

# get Ports
EndPointIDs = AdminConfig.getid('/EndPoint:/')
arrayEndPointIDs = EndPointIDs.split(lineSeparator)
NamedEndPointIDs = AdminConfig.getid('/NamedEndPoint:/')
arrayNamedEndPointIDs = NamedEndPointIDs.split(lineSeparator)

print arrayEndPointIDs
print '------------------------'
print NamedEndPointIDs
print '------------------------'
print arrayNamedEndPointIDs
print '------------------------'

# print
for x in range(len(arrayNodeIDs)):
        for y in range(len(arrayEndPointIDs)):
                if arrayEndPointIDs[y].find(AdminConfig.showAttribute(arrayNodeIDs[x],'name')) > 0:
                        print AdminConfig.showAttribute(arrayNodeIDs[x],'name'),AdminConfig.showAttribute(arrayNamedEndPointIDs[y],'endPointName'),AdminConfig.showAttribute(arrayEndPointIDs[y],'port')
                #endIf 
        #endFor - Endpoints by ID
#endFor - Nodes by ID
