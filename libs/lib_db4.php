<?php
/******************************************************************************************

lib_db4
Version: 4.1.0

Last changed: 15/Jul/2019
Coded by: Obrad, Tomas

 ******************************************************************************************/

define('DB_LOG_ERRORS',true);
define('DB_LOG_QUERIES',false);
define('DB_LOG_FILE','/tmp/dberr.log');

/**
 * Class EDatabase
 */
class EDatabase extends Exception{
	/**
	 * EDatabase constructor.
	 * @param int $code
	 * @param string $message
	 * @param string $query
	 * @param bool $mute
	 */
	function __construct($code, $message, $query='', $mute=false){
		parent::__construct($message, $code);

		if($mute) return;

		$last_row = null;

		$trace = $this->getTrace();

		foreach($trace as $trace_row){
			if(empty($trace_row['class']) || $trace_row['class'] != 'Database4')
				break;

			$last_row = $trace_row;
		}

		$script_location = $last_row['file'].' ('.$last_row['line'].')';

		if(DB_LOG_ERRORS){
			$s='[------- '.date('j-m-Y H:i:s')." --------------------------------------------------------]\n".
				"DATABASE ERROR ($code): $message\n".
				//"_SERVER: ".print_r($_SERVER, true)."\n".
				"File: $script_location\n";
			if($query!='') $s.="QUERY: $query\n";
			@file_put_contents(DB_LOG_FILE, $s, FILE_APPEND);
		}
	}
}

/**
 * Interface DatabaseDriver
 *
 * All adapters extend this interface
 */
interface DatabaseDriver{

	/**
	 * Connects to database server
	 *
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param bool $usessl
	 * @return bool true on success, false on failure.
	 */
	function connect($server,$username,$password,$usessl);

	/**
	 * Establishes DB connection
	 *
	 * @return bool true if connected, false otherwise
	 */
	function connected();

	/**
	 * Closes database connection
	 *
	 * @return bool true on success, false on failure.
	 */
	function close();

	/**
	 * Returns last error code
	 *
	 * @return int
	 */
	function errno();

	/**
	 * Returns last error message
	 *
	 * @return string
	 */
	function error();

	/**
	 * Selects database
	 *
	 * @param string $database DB name
	 * @return bool true on success, false on failure
	 */
	function select_db($database);

	/**
	 * Executes database query.
	 *
	 * @param string $sql
	 * @return mixed query result (which can be a resource or just true). Returns false on failure.
	 */
	function query($sql);

	/**
	 * @param mixed $resultset
	 * @return mixed Returns the next row as associative array or false if there is no more rows.
	 */
	function fetch_assoc(&$resultset);

	/**
	 * @param mixed $resultset
	 * @return mixed Returns the next row as array with text and numeric indexes or false if there is no more rows.
	 */
	function fetch_array(&$resultset);

	/**
	 * @return int Returns number of affected rows by the last query
	 */
	function affected_rows();

	/**
	 * @param mixed $resultset
	 * @return int Returns number of rows in the result set
	 */
	function num_rows(&$resultset);

	/**
	 * @return mixed Returns last insertion ID
	 */
	function insert_id();

	/**
	 * Frees memory used for resultset.
	 *
	 * @param mixed $resultset
	 * @return mixed
	 */
	function free_result(&$resultset);

	/**
	 * Escapes a string so it is suitable to be used in queries
	 *
	 * @param string $s
	 * @return string
	 */
	function escape($s);

	/**
	 * Escapes binary data
	 *
	 * @param string $s
	 * @return string
	 */
	function blob_escape($s);

	/**
	 * @param string $dbname
	 * @return bool true on success, false on failure
	 */
	function changedatabase($dbname);

	/**
	 * @return bool|null null if not supported
	 */
	function get_autocommit();

	/**
	 * @param bool $value
	 * @return bool|null null if not supported
	 */
	function set_autocommit($value);
}

/**
 * Class DDMySQL
 */
class DDMySQL implements DatabaseDriver{
	var $dbh;

	// Connects to database server
	// Returns true on success, false on failure.
	function connect($server,$username,$password,$usessl){
		$this->dbh = mysql_connect($server, $username, $password, false, ($usessl ? MYSQL_CLIENT_SSL : 0));
		return $this->dbh ? true : false;
	}

	// Returns true if connected, false otherwise
	function connected(){
		return !empty($this->dbh);
	}

	// Closes database connection
	// Returns true on success, false on failure.
	function close(){
		$result = mysql_close($this->dbh);
		$this->dbh = null;
		return $result;
	}

	// Returns last error code
	function errno(){
		return $this->dbh!==false ? mysql_errno($this->dbh) : mysql_errno();
	}

	// Returns last error message
	function error(){
		return $this->dbh!==false ? mysql_error($this->dbh) : mysql_error();
	}

	// Selects database
	// Returns true on success, false on failure.
	function select_db($database){
		return mysql_select_db($database, $this->dbh);
	}

	// Executes database query.
	// Returns query result (which can be a resource or just true). Returns false on failure.
	function query($sql){
		return mysql_query($sql, $this->dbh);
	}

	// Returns the next row as associative array or false if there is no more rows.
	function fetch_assoc(&$resultset){
		return mysql_fetch_assoc($resultset);
	}

	// Returns the next row as array with text and numeric indexes or false if there is no more rows.
	function fetch_array(&$resultset){
		return mysql_fetch_array($resultset);
	}

	// Returns number of affected rows by the last query
	function affected_rows(){
		return mysql_affected_rows($this->dbh);
	}

	// Returns number of rows in the result set
	function num_rows(&$resultset){
		return mysql_num_rows($resultset);
	}

	// Returns last insertion ID
	function insert_id(){
		return mysql_insert_id($this->dbh);
	}

	// Frees memory used for resultset.
	function free_result(&$resultset){
		$result = mysql_free_result($resultset);
		$resultset = null;
		return $result;
	}

	// Escapes a string so it is suitable to be used in queries
	function escape($s){
		return mysql_real_escape_string($s);
		//return addslashes($s);  // this is not safe
	}

	function blob_escape($s){
		return mysql_real_escape_string($s);
	}

	function changedatabase($dbname){
		return mysql_select_db($dbname, $this->dbh);
	}

	/**
	 * @return null
	 */
	function get_autocommit(){
		return null;
	}

	/**
	 * @param bool $value
	 * @return null
	 */
	function set_autocommit($value){
		return null;
	}

}


class DDMySQLi implements DatabaseDriver{
	var $dbh;

	// Connects to database server
	// Returns true on success, false on failure.
	function connect($server,$username,$password,$usessl){

		$port = null;

		if(strpos($server, ':'))
			list($server, $port) = explode(':', $server,2);

		$this->dbh = mysqli_connect($server, $username, $password, '', $port);

		//todo: implement ssl
		//if($usessl){ }

		return $this->dbh ? true : false;
	}

	// Returns true if connected, false otherwise
	function connected(){
		return !empty($this->dbh);
	}

	// Closes database connection
	// Returns true on success, false on failure.
	function close(){
		$result = mysqli_close($this->dbh);
		$this->dbh = null;
		return $result;
	}

	// Returns last error code
	function errno(){
		return $this->dbh!==false ? mysqli_errno($this->dbh) : mysqli_connect_errno($this->dbh);
	}

	// Returns last error message
	function error(){
		return $this->dbh!==false ? mysqli_error($this->dbh) : mysqli_connect_error($this->dbh);
	}

	// Selects database
	// Returns true on success, false on failure.
	function select_db($database){
		return mysqli_select_db($this->dbh, $database);
	}

	// Executes database query.
	// Returns query result (which can be a resource or just true). Returns false on failure.
	function query($sql){
		return mysqli_query($this->dbh, $sql);
	}

	// Returns the next row as associative array or false if there is no more rows.
	function fetch_assoc(&$resultset){
		return mysqli_fetch_assoc($resultset);
	}

	// Returns the next row as array with text and numeric indexes or false if there is no more rows.
	function fetch_array(&$resultset){
		return mysqli_fetch_array($resultset);
	}

	// Returns number of affected rows by the last query
	function affected_rows(){
		return mysqli_affected_rows($this->dbh);
	}

	// Returns number of rows in the result set
	function num_rows(&$resultset){
		return mysqli_num_rows($resultset);
	}

	// Returns last insertion ID
	function insert_id(){
		return mysqli_insert_id($this->dbh);
	}

	// Frees memory used for resultset.
	function free_result(&$resultset){

		mysqli_free_result($resultset);

		$resultset = null;

		// this fixes multicalls caused by procedure calls
		while(mysqli_more_results($this->dbh) && mysqli_next_result($this->dbh)){
			//$result = mysqli_use_result($this->dbh);
			//if(!empty($result))
			//  mysqli_free_result($result);
			//unset($result);
		}

		return true;
	}

	// Escapes a string so it is suitable to be used in queries
	function escape($s){
		return mysqli_real_escape_string($this->dbh, $s);
	}

	function blob_escape($s){
		return mysqli_real_escape_string($this->dbh, $s);
	}

	function changedatabase($dbname){
		return mysqli_select_db($this->dbh, $dbname);
	}

	/**
	 * @return bool
	 */
	function get_autocommit()
	{
		$result = $this->query('SELECT @@autocommit;');
		$row = mysqli_fetch_row($this->dbh, $result);
		return $row[0] ? true : false;
	}

	/**
	 * @param bool $value
	 * @return bool
	 */
	function set_autocommit($value)
	{
		return mysqli_autocommit($this->dbh, $value ? true : false);
	}
}

class DDSybase implements DatabaseDriver{
	var $dbh;

	// Connects to database server
	// Returns true on success, false on failure.
	function connect($server,$username,$password,$usessl){
		$this->dbh = sybase_connect($server, $username, $password);
		return $this->dbh ? true : false;
	}

	// Returns true if connected, false otherwise
	function connected(){
		return !empty($this->dbh);
	}

	// Closes database connection
	// Returns true on success, false on failure.
	function close(){
		$result = sybase_close($this->dbh);
		$this->dbh = null;
		return $result;
	}

	// Returns last error code
	function errno(){
		return 1;
	}

	// Returns last error message
	function error(){
		return sybase_get_last_message();
	}

	// Selects database
	// Returns true on success, false on failure.
	function select_db($database){
		return sybase_select_db($database, $this->dbh);
	}

	// Executes database query.
	// Returns query result (which can be a resource or just true). Returns false on failure.
	function query($sql){
		return sybase_query($sql, $this->dbh);
	}

	// Returns the next row as associative array or false if there is no more rows.
	function fetch_assoc(&$resultset){
		return sybase_fetch_array($resultset);
	}

	// Returns the next row as array with text and numeric indexes or false if there is no more rows.
	function fetch_array(&$resultset){
		return sybase_fetch_array($resultset);
	}

	// Returns number of affected rows by the last query
	function affected_rows(){
		return sybase_affected_rows($this->dbh);
	}

	// Returns number of rows in the result set
	function num_rows(&$resultset){
		return sybase_num_rows($resultset);
	}

	// Returns last insertion ID
	function insert_id(){
		return false;
	}

	// Frees memory used for resultset.
	function free_result(&$resultset){
		$result = sybase_free_result($resultset);
		$resultset = null;
		return $result;
	}

	// Escapes a string so it is suitable to be used in queries
	function escape($s){
		$result = addslashes($s);
		$result = str_replace("\\'","''",$result);
		return $result;
	}

	function blob_escape($s){
		return $this->escape($s);
	}

	function changedatabase($dbname){
		return sybase_select_db($dbname, $this->dbh);
	}

	/**
	 * @return null
	 */
	function get_autocommit()
	{
		return null;
	}

	/**
	 * @param bool $value
	 * @return null
	 */
	function set_autocommit($value)
	{
		return null;
	}
}

/**
 * Class Database4ServerType
 */
abstract class Database4ServerType{
	const MYSQL = 'mysql';
	const MYSQLI = 'mysqli';
	const SYBASE = 'sybase';
}

// Provides standardized way for accessing database
class Database4{
	/** @var DatabaseDriver $dd Driver instance */
	var $dd;

	/** @var string $server */
	var $server;

	/** @var string $username */
	var $username;

	/** @var string $password */
	var $password;

	/** @var string $database */
	var $database;

	/** @var bool $usessl */
	var $usessl;

	/** @var bool $connected */
	var $connected;

	/** @var array $muteerrors */
	var $muteerrors;

	/** @var bool $queryfirstassoc */
	protected $queryfirstassoc = false;

	/**
	 * Database4 constructor.
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param string $servertype
	 * @param bool $usessl
	 * @throws EDatabase
	 */
	function __construct($server='', $username='', $password='', $database='', $servertype='mysql', $usessl=false){
		$this->muteerrors = array();

		switch(strtolower($servertype)){
			case Database4ServerType::MYSQL: $this->dd = new DDMySQL(); break;
			case Database4ServerType::MYSQLI: $this->dd = new DDMySQLi(); break;
			case Database4ServerType::SYBASE: $this->dd = new DDSybase(); break;
			default: throw new EDatabase(-1,'Database server type "'.$servertype.'" not supported');
		}

		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->usessl = $usessl;
	}

	/**
	 * Terminate connection on object destruction
	 */
	function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Connects to the database server and selects database
	 *
	 * If parameters are not specified, then parameters used last time will be used.
	 * NOTE: You do not need to call this function explicitly.
	 *
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @return void Returns void. On an error, throws EDatabase.
	 * @throws EDatabase
	 */
	function connect($server='', $username='', $password='', $database=''){
		if($this->connected) $this->disconnect();

		if($server=='') $server = $this->server;
		if($username=='') $username = $this->username;
		if($password=='') $password = $this->password;
		if($database=='') $database = $this->database;

		if(!$this->dd->connect($server, $username, $password, $this->usessl)) $this->_throw_error('CONNECT '.$server." ERROR: ".$this->dd->errno()." ".$this->dd->error());
		$this->connected = true;

		if(!$this->dd->select_db($database)) {
			$this->disconnect();
			$this->_throw_error('CHANGEDB '.$database);
		}

		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
	}

	/**
	 * Closes mysql connection
	 */
	function disconnect(){
		if ($this->connected) {
			$this->dd->close();
			$this->connected = false;
		}
	}

	/**
	 * @param int $errcode
	 */
	function muteerr($errcode){
		if(isset($this->muteerrors[$errcode]))
			$this->muteerrors[$errcode]++;
		else
			$this->muteerrors[$errcode]=1;
	}

	/**
	 * Mute err 1062: Duplicate entry
	 */
	function muteduperr(){
		$this->muteerr(1062);
	}

	/**
	 * Throws last error
	 *
	 * @param string $query
	 * @param null|int $errcode
	 * @param null|string $errmsg
	 * @throws EDatabase
	 */
	function _throw_error($query='', $errcode = null, $errmsg = null){
		if(!$errcode)
			$errcode = $this->dd->errno();

		if(!$errmsg)
			$errmsg = $this->dd->error();

		if(isset($this->muteerrors[$errcode])){
			$mute = $this->muteerrors[$errcode]--;

			if($mute==1)
				unset($this->muteerrors[$errcode]);
		} else {
			$mute = false;
		}

		throw new EDatabase($errcode, $errmsg, $query, $mute);
	}

	/**
	 * Builds query by escaping and embeding parameters automatically.
	 *
	 * @param string $query
	 * @param array $PARAMS
	 * @param int $index
	 * @return string
	 */
	function _build_query($query, &$PARAMS, $index){
		$paramcount = count($PARAMS);
		$len = strlen($query);
		for($i=0; $i<$len && $index<$paramcount; ++$i)
			if($query[$i]=='?' || $query[$i]=='#'){
				$blob_escape = ($i<$len-1) && ($query[$i+1]=='?') ? 1 : 0;
				if($query[$i]=='?') {
					//if (!$blob_escape) $value = "'".$this->escape($PARAMS[$index])."'"; else
					//		   $value = "'".$this->dd->blob_escape($PARAMS[$index])."'";
					$value = "'".$this->escape($PARAMS[$index])."'";
				} else {
					//$value = (float) $PARAMS[$index];
					//$value = (string) $value;
					$value = $PARAMS[$index];
					if(is_string($value)) {
						$value = trim($value);
						$lvalue = strtolower($value);
						if ($lvalue=='true') $value = 1; else
							if ($lvalue=='false') $value = 0;
					}
					if(!is_numeric($value)) $value = intval($value);
					$value = (string)$value;
				}
				$query = substr($query,0,$i).$value.substr($query,$i+1+$blob_escape);
				$len = strlen($query);
				$i += strlen($value)-1-$blob_escape;
				++$index;
			}
		//	file_put_contents('/tmp/dbg.txt',$query."\n\n",FILE_APPEND);

		// Log query along with script names
		if(DB_LOG_QUERIES){
			$x = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$s = '';
			foreach($x as $g) $s.=$g['file'].':'.$g['line']."\n";
			file_put_contents('/tmp/sql.log',date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR']."\n".$s."\n".$query."\n=================================================\n",FILE_APPEND);
		}

		return $query;
	}

	/**
	 * @param string $sql
	 * @return mixed Returns sql query result
	 * @throws EDatabase
	 */
	function query($sql){
		if(!$this->connected)
			$this->connect();

		if(func_num_args() > 1) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 1);
		}

		$result = $this->dd->query($sql);
		if($result!==false) {
			$this->muteerrors = array();
			return $result;
		}

		$this->_throw_error($sql);
	}

	/**
	 * Queues a query to be run in a batch
	 *
	 * @param string $sql
	 * @throws EDatabase
	 */
	function queryqueue($sql){
		if(!$this->connected)
			$this->connect();

		if(func_num_args() > 1) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 1);
		}

		file_put_contents('/tmp/queryqueue.txt',$sql."\n",FILE_APPEND | LOCK_EX);
	}

	/**
	 * Returns sql query result. $sql is a raw query, no parameters are accepted nor escaped.
	 *
	 * THIS FUNCTION IS DANGEROUS !
	 *
	 * @param string $sql
	 * @return mixed
	 * @throws EDatabase
	 */
	function queryraw($sql){
		if(!$this->connected)
			$this->connect();

		$result = $this->dd->query($sql);

		if($result!==false)
			return $result;

		$this->_throw_error($sql);
	}

	/**
	 * @param bool $val
	 */
	function setQueryFirstAssoc($value){
		$this->queryfirstassoc = $value == true;
	}

	/**
	 * @param string $sql
	 * @return array|mixed Returns the first row of the query defined by $sql as associative array. Returns false in case no row was found.
	 * @throws EDatabase
	 */
	function queryfirst($sql){
		if(!$this->connected)
			$this->connect();

		if(func_num_args() > 1) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 1);
		}

		$qres = $this->dd->query($sql);

		if($qres===false)
			$this->_throw_error($sql);

		if($this->queryfirstassoc)
			$result = $this->dd->fetch_assoc($qres);
		else
			$result = $this->dd->fetch_array($qres);

		$this->dd->free_result($qres);
		return $result;
	}

	/**
	 * @param string $sql
	 * @return mixed|null Returns the first row and first column value of the query. Returns null in case no row was found
	 * @throws EDatabase
	 */
	function fetch($sql){
		if(!$this->connected)
			$this->connect();

		if(func_num_args() > 1) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 1);
		}

		$result = $this->queryfirst($sql);
		return is_array($result) ? $result[0] : null;
	}

	/**
	 * Returns all records in $result.
	 *
	 * NOTE: use with care as this will read all data at once and store in memory
	 *
	 * @param string $sql
	 * @param mixed $result
	 * @param string $column
	 * @throws EDatabase
	 */
	function queryall($sql, &$result, $column=''){

		if(!$this->connected)
			$this->connect();

		if(func_num_args() > 3) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 3);
		}

		$qres = $this->dd->query($sql);
		if(!$qres)
			$this->_throw_error($sql);

		$result = array();
		if($column!=''){
			if(preg_match('/^%%(.*)$/', $column, $match)) { // Tomas' weird grouping implementation
				$by_column = $match[1];
				while($row = $this->dd->fetch_assoc($qres)) {
					$by_column_val = $row[$by_column];

					// if this index already exists, we will use array
					if(isset($result[$by_column_val])){
						if(is_array($result[$by_column_val][0]))
							$result[$by_column_val][] = $row; else // already array - just append new row
							$result[$by_column_val] = array($result[$by_column_val], $row); // convert to array
					} else {
						$result[$by_column_val] = $row;
					}
				}
			} else if(preg_match('/^%(.*?)(?:@(.*))?$/', $column, $match)) { // group values by specific column value
				$by_column = $match[1];
				if(!empty($match[2])){
					$extract_col = $match[2];
					while($row = $this->dd->fetch_assoc($qres)) $result[$row[$by_column]][] = $row[$extract_col];
				} else {
					while($row = $this->dd->fetch_assoc($qres)) $result[$row[$by_column]][] = $row;
				}
			} else {
				while($row = $this->dd->fetch_assoc($qres)) $result[] = $row[$column];
			}

		} else {
			while($row = $this->dd->fetch_assoc($qres)) $result[] = $row;
		}
		$this->dd->free_result($qres);
	}

	/**
	 * Returns constructed query for test only. No query is executed.
	 *
	 * @param string $sql
	 * @return string
	 * @throws EDatabase
	 */
	function querytest($sql){
		if(!$this->connected) $this->connect(); // we need to connect as escaping function doesn't work otherwise
		if(func_num_args()>1) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 1);
		}
		return $sql;
	}

	/**
	 * Returns the number of rows affected by the last query
	 *
	 * @return int
	 */
	function recordsaffected(){
		return $this->dd->affected_rows();
	}

	/**
	 * Returns ID of the last inserted record, false on failure
	 *
	 * @return mixed
	 */
	function lastinsertid(){
		return $this->dd->insert_id();
	}

	/**
	 * Returns number of rows in the result set $queryresult returned by query() method. False on failure
	 *
	 * @param $queryresult
	 * @return int
	 */
	function recordcount(&$queryresult){
		if(!$queryresult) return 0;
		return $this->dd->num_rows($queryresult);
	}

	/**
	 * @param string $sql
	 * @return int
	 * @throws EDatabase
	 */
	function num_rows($sql){
		if(!$this->connected)
			$this->connect();

		if(func_num_args() > 1) {
			$PARAMS = func_get_args();
			$sql = $this->_build_query($sql, $PARAMS, 1);
		}

		return $this->dd->num_rows($this->dd->query($sql));
	}

	/**
	 * Returns the next record from the result set $queryresult returned by the query() method
	 *
	 * @param $queryresult
	 * @return array|bool|mixed Returns fase if there are no more records
	 */
	function nextrecord(&$queryresult){
		if(!$queryresult) return false;
		$row=$this->dd->fetch_assoc($queryresult);
		if(!$row) $this->dd->free_result($queryresult);
		return $row;
	}

	/**
	 * Escapes a string so it can be used safely in database query
	 *
	 * @param string $s
	 * @return string
	 * @throws EDatabase
	 */
	function escape($s){
		if(!$this->connected) $this->connect(); // some escaping functions need connection first
		return $this->dd->escape($s);
	}

	/**
	 * @return bool|null
	 * @throws EDatabase
	 */
	function get_autocommit(){
		if(!$this->connected) $this->connect(); // some escaping functions need connection first
		$result = $this->dd->get_autocommit();

		if($result === null){
			$this->_throw_error("GET autocommit not supported");
		}

		return $result;
	}

	/**
	 * @param bool $status
	 * @return bool|null
	 * @throws EDatabase
	 */
	function set_autocommit($status){
		if(!$this->connected) $this->connect(); // some escaping functions need connection first
		$result = $this->dd->set_autocommit($status);

		if($result === null){
			$this->_throw_error("SET autocommit not supported");
		}

		return $result;
	}

	/**
	 * Change currently used DB
	 *
	 * @param string $dbname
	 * @return bool
	 */
	function changedatabase($dbname){
		// if connected, we have to call select_db()
		if($this->connected){
			return $this->dd->changedatabase($dbname);
		}else{ // if not, we just change name
			$this->database = $dbname;
			return true;
		}
	}
}

?>
