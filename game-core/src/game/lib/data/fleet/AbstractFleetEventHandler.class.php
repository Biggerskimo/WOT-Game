<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/message/MessageEditor.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOventEditor.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventHandler.class.php');
require_once(LW_DIR.'lib/util/LockUtil.class.php');

/**
 * Provides functions for a better handling of wot events of fleets.
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2010 Lost Worlds <http://lost-worlds.net>
 */
abstract class AbstractFleetEventHandler extends Fleet implements WOTEventHandler {
	protected $missionID;
	
	protected $eventData = array();
	
	protected $searches = array();
	protected $replaces = array();
	
	/**
	 * Switches between two modes: the impact and the return 'mode'.
	 * 
	 * @param	array	event data
	 */
    public function execute($data) {
		// TODO: remove this passage?
	    // lock management
	    echo "l";
    	do {
	    	try {
				LockUtil::checkLock($this->ownerID);
				LockUtil::checkLock($this->ofiaraID);
				
				// everything checked
				break;
	    	}
	    	catch(SystemException $e) {
	    		echo 'waiting 0.5s because of a lock ...';
	    		
	    		usleep(500000);
	    	}
    	} while(true);
    	echo "m";
		
		LockUtil::setLock($this->ownerID, 10);
		if($this->ofiaraID) {
			LockUtil::setLock($this->ofiaraID, 10);
		}
		
		// execute
    	$this->initArrays();
    	
    	$this->eventData = $data;
    	
    	// return
    	if($data['state'] == 1) {
    		if(count($this->fleet)) {
	    		$this->executeReturn();
	    		$this->sendReturnMessage();
    		}
    	}
    	// impact
    	else if($data['state'] == 0) {
    		$this->executeImpact();
			$this->sendImpactOwnerMessage();
			$this->sendImpactOfiaraMessage();
    	}
    	// other states
    	else {
    		$this->executeUnknownEvent();
    	}
    	
    	// TODO: integrate this in wcf event listener?
    	FleetOvent::update($this);
		
    	// lock management
		LockUtil::removeLock($this->ownerID);
		LockUtil::removeLock($this->ofiaraID);
    	return;
    }
    
    /**
     * Executes the impact event.
     */
    abstract protected function executeImpact();
    
    /**
     * Executes the return event.
     */
    protected function executeReturn() {
		$this->getStartPlanet()->getEditor()->changeLevel($this->fleet);
    	$this->getStartPlanet()->getEditor()->changeResources($this->metal, $this->crystal, $this->deuterium);
		$this->getEditor()->delete();
    }
    
    /**
     * Executes a for AbstractFleetEventHandler unknown event (neither state 0 nor 1)
     */
    protected function executeUnknownEvent() {
    	// does nothing.
    }
    
    /**
     * Returns the data for the impact event message of the owner.
     * It may return null, if no message should be sent.
  	 * 
  	 * @return	mixed	message data
     */
	protected function getImpactOwnerMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.owner'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.owner.subject'),
				'text' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.owner.text'),
			);
		
		return $messageData;    	
    }
    
    /**
     * Returns the data for the impact event message of the ofiara.
     * It may return null, if no message should be sent.
  	 * 
  	 * @return	mixed	message data
     */
	protected function getImpactOfiaraMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.ofiara'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.ofiara.subject'),
				'text' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.ofiara.text'),
			);
		
		return $messageData;    	
    }
    
    /**
     * Returns the data for the return event message (only for the owner).
     * It may return null, if no message should be sent.
  	 * 
  	 * @return	mixed	message data
     */
    protected function getReturnMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.owner'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.return.subject'),
				'text' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.return.text'),
			);
		
		return $messageData;    	
    }
    
    /**
     * Initializes the arrays for parsing texts.
     */
    protected function initArrays() {
    	if(count($this->searches)) {
    		return;
    	}
    	$this->searches = array(
    		'{sPName}', // 1
    		'{sPKoords}', // 2
    		'{ePName}', // 3
    		'{ePKoords}', // 4
    		'{ressources}', // 5
    		// the replaces above are depricated. use better the replaces below
    		'{$startPlanet}', // 6
    		'{$startPlanetCoordinates}', // 7
    		'{$targetPlanet}', // 8
    		'{$targetPlanetCoordinates}', // 9
    		'{$resources}', // 10
    		'{$shipsList}' // 11
    	);
    	$this->replaces = array(
    		$this->getStartPlanet(), // 1
    		$this->getStartPlanet()->getLinkedCoordinates(), // 2
    		$this->getTargetPlanet(), // 3
    		$this->getTargetPlanet()->getLinkedCoordinates(), // 4
    		$this->getRessources('strWBR'), // 5
    		
    		$this->getStartPlanet(), // 6
    		$this->getStartPlanet()->getLinkedCoordinates(), // 7
    		$this->getTargetPlanet(), // 8
    		$this->getTargetPlanet()->getLinkedCoordinates(), // 9
    		$this->getRessources('strWBR'), // 10
    		$this->getShips('strWBR') // 11    		
    	);
    }
    
    /**
     * Replaces some textparts.
     * 
     * @param	string	text to parse
     */
    protected function parse($text) {
    	$this->initArrays();
    	
    	$text = str_replace($this->searches, $this->replaces, $text);
    	
    	return $text;
    }
    
    /**
     * Sends the message to the owner of the fleet on impact event.
     */
    protected function sendImpactOwnerMessage() {
    	$messageData = $this->getImpactOwnerMessageData();

    	if($messageData !== null) {
    		$messageData = $this->parse($messageData);
    		
    		MessageEditor::create($this->ownerID, $messageData['subject'], $messageData['text'], 0, $messageData['sender'], 0);
    	}
    }
    
    /**
     * Sends the message to the ofiara of the fleet on impact event.
     */
    protected function sendImpactOfiaraMessage() {
    	$messageData = $this->getImpactOfiaraMessageData();

    	if($messageData !== null) {
    		$messageData = $this->parse($messageData);
    		
    		MessageEditor::create($this->ofiaraID, $messageData['subject'], $messageData['text'], 0, $messageData['sender'], 0);
    	}
    }
    
    /**
     * Sends the message to the ofiara of the fleet on return event.
     */
    protected function sendReturnMessage() {
    	$messageData = $this->getReturnMessageData();

    	if($messageData !== null) {
    		$messageData = $this->parse($messageData);
    		
    		MessageEditor::create($this->ownerID, $messageData['subject'], $messageData['text'], 0, $messageData['sender'], 0);
    	}
    }
}
?>