<?php

class Cluster extends ArrayIterator {


	/**
	 *
	 * @param string $serverID - md5 of the host and port values
	 * @return Server
	 */
	public function getServer($serverID) {
		$this->rewind();
		while($this->valid()) {
			if($serverID == $this->current()->getUniqueID() ) {
				return $this->current();
			}
    		$this->next();
		}
		return false;
	}


	public function jsonSerialize() {
		$this->rewind();
		$result = array();
		while($this->valid()) {
			$result[] = $this->current()->jsonSerialize();
    		$this->next();
		}
		return $result;
	}
}
