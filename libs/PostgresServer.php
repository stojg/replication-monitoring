<?php

class PostgresServer extends Server {

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
	protected $isMaster, $isSlave = null;

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
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 *
	 * @return string
	 */
	public function getHostAndPort() {
		return $this->host.':'.$this->port;
	}

	public function row($sql) {
		if(!$this->conn) { return false; }
		return pg_fetch_assoc($this->query($sql));
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

	public function getID() {
		return $this->host.':'.$this->port;
	}

	public function getCurrentLogPos() {
		if($this->canReplicate()) {
			return 'n/a';
		}
		$data = $this->row('select pg_current_xlog_location();');
		return $data['pg_current_xlog_location'];
	}
	/**
	 *
	 * @return boolean
	 */
	public function getHotStandByMode() {
		$res = $this->row("SHOW hot_standby;");
		return $res['hot_standby'];
	}

	/**
	 * @return bool
	 */
	public function isMaster() {
		if($this->isMaster !== null) {
			return $this->isMaster;
		}
		return $this->isMaster = (bool)$this->row('SHOW hot_standby;');
	}

	/**
	 *
	 * @return bool
	 */
	public function isSlave() {
		if($this->isSlave !== null) {
			return $this->isSlave;
		}
		$data = $this->row('SELECT pg_last_xlog_replay_location()');
		if($data['pg_last_xlog_replay_location']) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function canReplicate() {
		$data = $this->row('SELECT pg_is_in_recovery();');
		if($data['pg_is_in_recovery'] === 'f') {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isReplicating() {
		if(!$this->canReplicate()) {
			return false;
		}
		$data = $this->row('SELECT pg_is_xlog_replay_paused();');
		if($data['pg_is_xlog_replay_paused'] === 't') {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return type
	 */
	public function startReplication() {
		return $this->command('select pg_xlog_replay_resume();');
		
	}

	/**
	 *
	 * @return type
	 */
	public function pauseReplication() {
		return $this->command('select pg_xlog_replay_pause();');
	}

	/**
	 * Returns a list of replication slaves currently registered with the master.
	 *
	 * @return array
	 */
	public function getAttachedSlaves() {
		$data = $this->getList('SELECT pg_stat_replication.* FROM pg_stat_replication;');
		return $this->tableFlip($data);
	}

	/**
	 *
	 * @return array
	 */
	public function getSlaveStatus() {
		if(!$this->canReplicate()) {
			return false;
		}
		$data = $this->row('SELECT pg_last_xlog_receive_location();');
		$data += $this->row('SELECT pg_last_xlog_replay_location();');
		return $data;
	}

	/**
	 *
	 * @return resource
	 */
	protected function connect() {
		$connString = 'host='.$this->host.' port='.$this->port.' user='.$this->user.' password='.$this->password.' dbname=template1';
		$con = @pg_connect($connString);
		if(!$con) {
			return false;
		}
		return $con;
	}

	/**
	 *
	 * @param string $sql
	 * @return pg_resource
	 */
	protected function query($sql) {
		if(!$this->conn) { return false; }
		$res = pg_query($this->conn, $sql);
		if(pg_last_error($this->conn)) {
			logP(pg_last_error($this->conn));
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
		if(!pg_num_rows($res)) {
			return $list;
		}

		while($row = pg_fetch_assoc($res)) {
			$list[] = $row;
		}
		return $list;
	}
}