<?php

class MySQLServer extends Server {

	/**
	 *
	 * @var string
	 */
	protected $host, $user, $password, $port;

	/**
	 *
	 * @var resource
	 */
	protected $conn = null;

	/**
	 *
	 * @var bool
	 */
	protected $isMaster, $isSlave = null, $slaveData;

	/**
	 *
	 * @param string $host
	 * @param string $user
	 * @param String $password
	 */
	public function __construct($host, $port, $user, $password) {
		$this->host = $host;
		$this->user = $user;
		$this->port = $port;
		$this->password = $password;
		$this->conn = $this->connect();
	}

	/**
	 *
	 * @return type
	 */
	public function getConn() {
		return $this->conn;
	}

	/**
	 *
	 * @return type
	 */
	public function getHostAndPort() {
		return $this->host.':'.$this->port;
	}

	/**
	 *
	 * @param type $sql
	 * @return boolean
	 */
	public function row($sql) {
		if(!$this->conn) { return false; }
		return mysql_fetch_assoc($this->query($sql));
	}

	/**
	 *
	 * @param type $sql
	 * @return boolean
	 */
	public function command($sql) {
		if(!$this->query($sql)) {
			logP('[!] Command "'.$sql.'" failed');
			exit(1);
		}
		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getID() {
		$id = $this->row("SHOW variables LIKE 'server_id';");
		return $id['Value'];
	}

	/**
	 *
	 * @return boolean 
	 */
	public function canReplicate() {
		$res = $this->row("SHOW variables like 'read_only';");
		if($res['Value'] === 'OFF') {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getHotStandByMode() {
		$res = $this->row("SHOW variables like 'read_only';");
		return $res['Value'];
	}

	/**
	 *
	 * @return type
	 */
	public function getCurrentLogPos() {
		$res = $this->row("SHOW MASTER STATUS;");
		return $res['File'].':'.$res['Position'];
	}

	/**
	 * @return bool
	 */
	public function isMaster() {
		if($this->isMaster !== null) {
			return $this->isMaster;
		}
		return $this->isMaster = (bool)$this->row('SHOW SLAVE HOSTS;');
	}

	/**
	 *
	 * @return bool
	 */
	public function isReplicating() {
		$data = ($this->row('SHOW SLAVE STATUS;'));
		if($data['Slave_IO_Running'] === 'No') {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	public function isSlave() {
		if($this->isSlave !== null) {
			return $this->isSlave;
		}
		return $this->isSlave = (bool)$this->row('SHOW SLAVE STATUS;');
	}

	/**
	 *
	 * @return boolean
	 */
	public function startReplication() {
		if(!$this->isSlave()) {
			return false;
		}
		return $this->command('START SLAVE;');
	}

	/**
	 *
	 * @return boolean
	 */
	public function pauseReplication() {
		if(!$this->isSlave()) {
			return false;
		}
		return $this->command('STOP SLAVE;');
	}

	/**
	 *
	 * @return array
	 */
	public function getMasterData() {
		return $this->row('SHOW MASTER STATUS;');
	}

	/**
	 * Returns a list of replication slaves currently registered with the master.
	 */
	public function getAttachedSlaves() {
		$data = $this->getList('SHOW SLAVE HOSTS;');
		return $this->tableFlip($data);
	}

	/**
	 *
	 * @return array || false
	 */
	public function getSlaveStatus() {
		$interestingStats = array(
			'Slave_IO_Running',
			'Slave_SQL_Running',
			'Master_Server_Id',
			'Master_Host',
			'Master_User',
		#	'Slave_IO_State',
			'Master_Log_File',
			'Read_Master_Log_Pos',
			'Last_Errno',
			'Last_Error',
			'Last_IO_Errno',
			'Last_IO_Error',
			'Last_SQL_Errno',
			'Last_SQL_Error',
			
		);
		$row = $this->row('SHOW SLAVE STATUS;');
		if(!$row) {
			return false;
		}
		$data = array();
		foreach($interestingStats as $statusName) {
			if(isset($row[$statusName])) {
				$data[$statusName] = $row[$statusName];
			}
		}
		return $data;
	}

	/**
	 *
	 * @return resource
	 */
	protected function connect() {
		$con = @mysql_connect($this->host.':'.$this->port, $this->user, $this->password);
		if(!$con) {
			return false;
		}
		return $con;
	}

	/**
	 *
	 * @param string $sql
	 * @return mysql_resource
	 */
	protected function query($sql) {
		if(!$this->conn) { return false; }
		$res = mysql_query($sql, $this->conn);
		if(mysql_errno($this->conn)) {
			logP(mysql_error($this->conn));
			exit(1);
		}
		return $res;
	}


	/**
	 *
	 * @param string $sql
	 * @return array
	 */
	protected function getList($sql) {
		if(!$this->conn) { return false; }
		$list = array();
		$res = $this->query($sql);
		if(!mysql_num_rows($res)) {
			return $list;
		}

		while($row = mysql_fetch_assoc($res)) {
			$list[] = $row;
		}
		return $list;
	}
}