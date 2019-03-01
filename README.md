# jeedom_ZigbeeShepherd
Plugin ZigbeeShepherd for Jeedom using the Generic HTTP connector 

1) Installer sur un serveur via nodesjs (ici sur le serveur 192.168.1.56:3000)

https://github.com/picsou83/zigbee-shepherd-api-rest

2) Installer le plugin sur votre jeedom

3) modifier le fichier jeedom_ZigbeeShepherd/core/class/zigbee.class.php

ligne 26 remplacer 192.168.1.56:3000 par votre addresse ou est installé zigbee-shepherd-api-rest
```  
$url = 'http://192.168.1.56:3000/genOnOff?appareil=' . $ieeeAddr . '&epid=' . $epId . '&commande=' . $value;
```  
![alt tag](https://user-images.githubusercontent.com/34648108/52850739-7c897180-3114-11e9-87f7-52ab5d3cfa96.png)

'devChange' and 'attReport' are sent to jeedom plugin
example of Get request :
```
{"Type":"attReport","Typedev":"Router","ieeeAddr":"0xd0cf5efffeb423d2","nwkAddr":44829,"manufId":4476,"manufName":"IKEA of Sweden","powerSource":"Mains (single phase)","modelId":"TRADFRI bulb E27 W opal 1000lm","epId":1,"data":{"cid":"genOnOff","data":{"onOff":0}}}
```
