# jeedom_ZigbeeShepherd
Plugin ZigbeeShepherd for Jeedom using the Generic HTTP connector 

![alt tag](https://user-images.githubusercontent.com/34648108/52850739-7c897180-3114-11e9-87f7-52ab5d3cfa96.png)

'devChange' and 'attReport' are sent to jeedom plugin
example of Get request :
```
{"Type":"attReport","Typedev":"Router","ieeeAddr":"0xd0cf5efffeb423d2","nwkAddr":44829,"manufId":4476,"manufName":"IKEA of Sweden","powerSource":"Mains (single phase)","modelId":"TRADFRI bulb E27 W opal 1000lm","epId":1,"data":{"cid":"genOnOff","data":{"onOff":0}}}
```
