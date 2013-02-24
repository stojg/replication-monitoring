<?php

class Server {
	
	/**
	 *
	 * @return array
	 */
	public function jsonSerialize() {
        return array(
			'id' => $this->getUniqueID(),
        	'hostAndPort' => $this->getHostAndPort(),
			'isConnected' => (bool)$this->getConn(),
			'canReplicate' => (bool)$this->canReplicate(),
			'isReplicating' => (bool)$this->isReplicating(),
        	'serverID' => $this->getID(),
        	'hotStandByMode' => $this->getHotStandByMode(),
        	'currentLogPos' => $this->getCurrentLogPos(),
			'connectedSlaves' => $this->getAttachedSlaves(),
			'slaveStatus' => $this->getSlaveStatus(),
        );
    }

	/**
	 *
	 * @return string
	 */
	public function getUniqueID() {
		return md5($this->getHostAndPort());
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	protected function tableFlip($data) {
		$result = array();
		if(!$data) {
			return array();
		}
		$headers = array_keys($data[0]);

		foreach($headers as $header) {
			$result[$header] = array();
			foreach($data as $server) {
				$result[$header][] = $server[$header];
			}
		}
		return $result;
	}
}