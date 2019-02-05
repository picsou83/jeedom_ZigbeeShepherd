<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'zigbee')) {
 echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (zigbee)', __FILE__);
 die();
}

//{"Type":"cmdOn","Typedev":"EndDevice","ieeeAddr":"0x000d6ffffe105c0a","nwkAddr":51769,"manufId":4476,"manufName":"IKEA of Sweden","powerSource":"Battery","modelId":"TRADFRI on/off switch","epId":1,
//"data":{"cid":"genOnOff","data":{}},"clusterid":"genOnOff","groupid":12,"linkquality":128}

$Type = init('Type');
$Typedev = init('Typedev');
$nwkAddr = init('nwkAddr');
$manufId = init('manufId');
$manufName = init('manufName');
$powerSource = init('powerSource');
$modelId = init('modelId');
$groupid = init('groupid');
$linkquality = init('linkquality');
$device = init('device');
$ip = init('ip');
$taskid = init('taskid');
$cmd = init('cmd');
$value = init('value');
$ieeeAddr = init('ieeeAddr');
$cid = init('cid');
$onOff= init('onOff');
$epId= init('epId');

$elogic = zigbee::byLogicalId($ieeeAddr, 'zigbee');

if (!is_object($elogic)) {
	if (config::byKey('include_mode','zigbee') != 1) {
		return false;
	}
	$elogic = new zigbee();
	$elogic->setEqType_name('zigbee');
	$elogic->setLogicalId($ieeeAddr);
	$elogic->setName($ieeeAddr);
	$elogic->setIsEnable(true);
	$elogic->setConfiguration('Typedev',$Typedev);
	$elogic->setConfiguration('manufId',$manufId);
    $elogic->setConfiguration('manufName',$manufName);
    $elogic->setConfiguration('powerSource',$powerSource);
  	$elogic->setConfiguration('modelId',$modelId);
	$elogic->setConfiguration('groupid',$groupid);
    $elogic->setConfiguration('linkquality',$linkquality);
  	$elogic->setConfiguration('ieeeAddr',$ieeeAddr);
	$elogic->setConfiguration('device',$ieeeAddr);
    $elogic->setConfiguration('epId',$epId);
    $elogic->setConfiguration('ip',$nwkAddr);
    $elogic->setConfiguration('cid',$cid);
	$elogic->save();
	event::add('zigbee::includeDevice',
	array(
		'state' => 1
	)
);
} else {
	if ($ieeeAddr != $elogic->getConfiguration('device')) {
		$elogic->setConfiguration('device',$ieeeAddr);
		$elogic->save();
	}
}

$cmdlogic = zigbeeCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cid);
 if ($cid === 'genBasic') {
return false;
} 

if (!is_object($cmdlogic)) {
	$cmdlogic = new zigbeeCmd();
	$cmdlogic->setLogicalId($cid);
	$cmdlogic->setName($cid);

  
if ($cid === 'genOnOff')  {
    $cmdlogic->setType('info');
	$cmdlogic->setSubType('binary');
} else if ($cid === 'msOccupancySensing') {
    $cmdlogic->setType('info');
	$cmdlogic->setSubType('binary');
}  else if ($cid === 'msIlluminanceMeasurement') {
    $cmdlogic->setType('info');
	$cmdlogic->setSubType('numeric');
} else if ($cid === 'genPowerCfg') {
    $cmdlogic->setType('info');
	$cmdlogic->setSubType('numeric');
}
	$cmdlogic->setEqLogic_id($elogic->getId());
	$cmdlogic->setConfiguration('cmd',$cid);
}
$cmdlogic->setConfiguration('value',$value);
$cmdlogic->event($value);
$cmdlogic->save();

return true;
?>