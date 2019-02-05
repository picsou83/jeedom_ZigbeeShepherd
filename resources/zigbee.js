var http = require('http'),
    url = require("url"),
    request = require('request'),
    ipaddr = process.argv[2],
    urlJeedom = process.argv[3],
    debug = process.argv[4] || 0;

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

// {"Type":"attReport","Typedev":"EndDevice","ieeeAddr":"0x00158d00029c011d","nwkAddr":53537,"manufId":4151,"manufName":"LUMI","powerSource":"Battery","modelId":"lumi.sensor_motion.aq2","epId":1,"data":{"cid":"msIlluminanceMeasurement","data":{"measuredValue":733}}}
//{"Type":"dataConfirm","Typedev":"Router","ieeeAddr":"0x000d6ffffe1079a5","nwkAddr":27389,"manufId":4476,"manufName":"IKEA of Sweden","powerSource":"Mains (single phase)","modelId":"TRADFRI control outlet","epId":1,"data":{"dstaddr":27389,"destendpoint":1,"srcendpoint":1,"clusterid":6,"transid":1,"options":48,"radius":30,"len":3,"data":{"type":"Buffer","data":[1,1,1]}}}

function answer(req, res) {
    var ipString = req.connection.remoteAddress;
 	let data = req.url.replace("/", "");  
//    console.log("Get request " + req.url);
//  	console.log(data);
  	let buff = new Buffer(data, 'base64');
    let text = buff.toString('ascii');  
  	let valeur = null;
//  	console.log('"' + data + '" converted from Base64 to ASCII is "' + text + '"');
	console.log("Get request " + text);
    obj = JSON.parse(text);
//	console.log(obj.ieeeAddr);
// 	console.log(obj.epId);
//	console.log(obj.data.data.onOff);
// 	console.log(obj.data.cid);

     if (obj.data.cid === 'genOnOff')  {
     	$valeur = obj.data.data.onOff;
      } else if (obj.data.cid === 'msOccupancySensing') {
  		$valeur = obj.data.data.occupancy;
      }  else if (obj.data.cid === 'msIlluminanceMeasurement') {
  		$valeur = obj.data.data.measuredValue;
      } else if (obj.data.cid === 'genPowerCfg') {
  		$valeur = obj.data.data.batteryPercentageRemaining;
      } 
  
  
  
    urlj = urlJeedom 	+ "&Type=" + obj.Type 
      					+ "&Typedev=" + obj.Typedev
      					+ "&ieeeAddr=" + obj.ieeeAddr 
      					+ "&nwkAddr=" + obj.nwkAddr 
      					+ "&manufId=" + obj.manufId 
      					+ "&manufName=" + obj.manufName
     					+ "&powerSource=" + obj.powerSource 
      					+ "&modelId=" + obj.modelId 
      					+ "&groupid=" + obj.groupid
    					+ "&linkquality=" + obj.linkquality
      					+ "&epId=" + obj.epId 
      					+ "&cid=" + obj.data.cid
      					+ "&value=" + $valeur;
    if (debug == 1) {console.log("Calling Jeedom " + urlj);}
  	request({
  		url: urlj,
  		method: 'PUT',
  	},
  	function (error, response, body) {
  		if (!error && response.statusCode == 200) {
  			if (debug == 1) {console.log((new Date()) + "Got response Value: " + response.statusCode);}
  		}else{
  			console.log((new Date()) + " - Error : "  + error );
  		}
  	});

    // HTTP response header - the content will be HTML MIME type
    res.writeHead(200, {'Content-Type': 'text/html'});

    // Write out the HTTP response body
    res.write('<html><body>' +
    '<h1>Jeedom receive<h1>'+
    '</body></html>');

    // End of HTTP response
    res.end();
}

/************************/
/*  START THE SERVER    */
/************************/

// Create the HTTP server
var server = http.createServer(answer);

// Turn server on - now listening for requests on localIP and port
server.listen(8122, ipaddr);

// print message to terminal that server is running
console.log('Server running');