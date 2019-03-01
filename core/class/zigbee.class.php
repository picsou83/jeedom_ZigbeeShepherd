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
* http://192.168.1.56:3000/genOnOff?commande=on&appareil='0x000d6ffffe7cc1e8'&epid=1
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';


class zigbee extends eqLogic {

  public static function sendCommand( $ieeeAddr, $epId, $value ) {
    $url = 'http://192.168.1.56:3000/genOnOff?appareil=' . $ieeeAddr . '&epid=' . $epId . '&commande=' . $value;
    $retour = file_get_contents($url);
  }

  public static function deamon_info() {
    $return = array();
    $return['log'] = 'zigbee_node';
    $return['state'] = 'nok';
    $pid = trim( shell_exec ('ps ax | grep "zigbee/resources/zigbee.js" | grep -v "grep" | wc -l') );
    if ($pid != '' && $pid != '0') {
      $return['state'] = 'ok';
    }
    $return['launchable'] = 'ok';
    return $return;
  }

  public static function deamon_start($_debug = false) {
    self::deamon_stop();
    $deamon_info = self::deamon_info();
    if ($deamon_info['launchable'] != 'ok') {
      throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
    }
    log::add('zigbee', 'info', 'Lancement du démon zigbee');

    $url = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/zigbee/core/api/jeezigbee.php?apikey=' . jeedom::getApiKey('zigbee');

    if ($_debug = true) {
      $log = "1";
    } else {
      $log = "0";
    }
    $sensor_path = realpath(dirname(__FILE__) . '/../../resources');

    $cmd = 'nice -n 19 nodejs ' . $sensor_path . '/zigbee.js ' . config::byKey('internalAddr') . ' ' . $url . ' ' . $log;

    log::add('zigbee', 'debug', 'Lancement démon zigbee : ' . $cmd);

    $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('zigbee_node') . ' 2>&1 &');
    if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
      log::add('zigbee', 'error', $result);
      return false;
    }

    $i = 0;
    while ($i < 30) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] == 'ok') {
        break;
      }
      sleep(1);
      $i++;
    }
    if ($i >= 30) {
      log::add('zigbee', 'error', 'Impossible de lancer le démon zigbee, vérifiez le port', 'unableStartDeamon');
      return false;
    }
    message::removeAll('zigbee', 'unableStartDeamon');
    log::add('zigbee', 'info', 'Démon zigbee lancé');
    return true;
  }

  public static function deamon_stop() {
    exec('kill $(ps aux | grep "/ezigbee.js" | awk \'{print $2}\')');
    log::add('zigbee', 'info', 'Arrêt du service zigbee');
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] == 'ok') {
      sleep(1);
      exec('kill -9 $(ps aux | grep "/zigbee.js" | awk \'{print $2}\')');
    }
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] == 'ok') {
      sleep(1);
      exec('sudo kill -9 $(ps aux | grep "/zigbee.js" | awk \'{print $2}\')');
    }
  }

  public static function dependancy_info() {
    $return = array();
    $return['log'] = 'zigbee_dep';
    $serialport = realpath(dirname(__FILE__) . '/../../resources/node_modules/http');
    $request = realpath(dirname(__FILE__) . '/../../resources/node_modules/request');
    $return['progress_file'] = '/tmp/zigbee_dep';
    if (is_dir($serialport) && is_dir($request)) {
      $return['state'] = 'ok';
    } else {
      $return['state'] = 'nok';
    }
    return $return;
  }

  public static function dependancy_install() {
    log::add('zigbee','info','Installation des dépéndances nodejs');
    $resource_path = realpath(dirname(__FILE__) . '/../../resources');
    passthru('/bin/bash ' . $resource_path . '/nodejs.sh ' . $resource_path . ' espeasy > ' . log::getPathToLog('espeasy_dep') . ' 2>&1 &');
  }

  public function preUpdate() {
    if ($this->getConfiguration('ieeeAddr') == '') {
      throw new Exception(__('L\'adresse ne peut etre vide',__FILE__));
    }
  }
  
  public function postUpdate() {
        log::add('zigbee', 'info', '** postUpdate CMD **');
    	$cmdlogic = zigbeeCmd::byEqLogicIdAndLogicalId($this->getId(), 'genOnOff');
    if ($this->getConfiguration('modelId') === 'TRADFRI control outlet' && is_object($cmdlogic)){
          $cmdlogicverif = zigbeeCmd::byEqLogicIdAndLogicalId($this->getId(), 'on');
          if (!is_object($cmdlogicverif)) {
          $jeezigbeeCmd = (new zigbeeCmd())
                    ->setName(__('On', __FILE__))
                    ->setEqLogic_id($this->id)
                    ->setLogicalId('on')
                    ->setConfiguration('request', 'on')
                    ->setType('action')
                    ->setSubType('other')
            		->setOrder(1)
                    ->setEventOnly(1)
                    ->setIsVisible(1);
          $jeezigbeeCmd->save();
        }
          $cmdlogicverif = zigbeeCmd::byEqLogicIdAndLogicalId($this->getId(), 'off');
          if (!is_object($cmdlogicverif)) {
          $jeezigbeeCmd = (new zigbeeCmd())
                    ->setName(__('Off', __FILE__))
                    ->setEqLogic_id($this->id)
                    ->setLogicalId('off')
                    ->setConfiguration('request', 'off')
                    ->setType('action')
                    ->setSubType('other')
            		->setOrder(2)
                    ->setEventOnly(1)
                    ->setIsVisible(1);
          $jeezigbeeCmd->save();
        }
          $cmdlogicverif = zigbeeCmd::byEqLogicIdAndLogicalId($this->getId(), 'toggle');
          if (!is_object($cmdlogicverif)) {
          $jeezigbeeCmd = (new zigbeeCmd())
                    ->setName(__('Toggle', __FILE__))
                    ->setEqLogic_id($this->id)
                    ->setLogicalId('toggle')
                    ->setConfiguration('request', 'toggle')
                    ->setType('action')
                    ->setSubType('other')
            		->setOrder(3)
                    ->setEventOnly(1)
                    ->setIsVisible(0);
          $jeezigbeeCmd->save();
        }
     }
   } 

  public function preSave() {
    $this->setLogicalId($this->getConfiguration('ieeeAddr'));
  }
}

class zigbeeCmd extends cmd {
  public function execute($_options = null) {
    switch ($this->getType()) {
      case 'info' :
      return $this->getConfiguration('value');
      break;
      case 'action' :
      $request = $this->getConfiguration('request');
      switch ($this->getSubType()) {
        case 'slider':
        $request = str_replace('#slider#', $_options['slider'], $request);
        break;
        case 'color':
        $request = str_replace('#color#', $_options['color'], $request);
        break;
        case 'message':
        if ($_options != null)  {
          $replace = array('#title#', '#message#');
          $replaceBy = array($_options['title'], $_options['message']);
          if ( $_options['title'] == '') {
            throw new Exception(__('Le sujet ne peuvent être vide', __FILE__));
          }
          $request = str_replace($replace, $replaceBy, $request);
        }
        else
        $request = 1;
        break;
        default : $request == null ?  1 : $request;
      }

      $eqLogic = $this->getEqLogic();

      zigbee::sendCommand(
      $eqLogic->getConfiguration('ieeeAddr') ,
      $eqLogic->getConfiguration('epId') ,
      $request );

      return $request;
    }
    return true;
  }

  public function preSave() {
    if ($this->getType() == "action") {
      $eqLogic = $this->getEqLogic();
      $name = $this->getName();
//      $eqLogic->getConfiguration('ieeeAddr')
      $this->setLogicalId($name);

//      log::add('zigbee','info','http://192.168.1.56:3000/genOnOff?appareil=' . $eqLogic->getConfiguration('ieeeAddr') . '&epid=' . $eqLogic->getConfiguration('epId') . '&commande=' . $this->getConfiguration('request'));
//      $this->setConfiguration('value', 'http://192.168.1.56:3000/genOnOff?appareil=' . $eqLogic->getConfiguration('ieeeAddr') . '&epid=' . $eqLogic->getConfiguration('epId') . '&commande=' . $this->getConfiguration('request'));
      //$this->save();
    }
  }

}
