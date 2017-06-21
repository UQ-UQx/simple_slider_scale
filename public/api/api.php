<?php

class MyApi
{
	/**
	 * Object containing all incoming request params
	 * @var object
	 */
	private $request;
	private $db;
	private $config;

	public function __construct($database, $config)
	{

		$this->db = $database;
		$this->config = $config;
		$this->_processRequest();

	}

	/**
	 * Routes incoming requests to the corresponding method
	 *
	 * Converts $_REQUEST to an object, then checks for the given action and
	 * calls that method. All the request parameters are stored under
	 * $this->request.
	 */
	private function _processRequest()
	{
		// prevent unauthenticated access to API
		$this->_secureBackend();

		// get the request
		if (!empty($_REQUEST)) {
			// convert to object for consistency
			$this->request = json_decode(json_encode($_REQUEST));
		} else {
			// already object
			$this->request = json_decode(file_get_contents('php://input'));
		}

		//check if an action is sent through
		if(!isset($this->request->action)){
			//if no action is provided then reply with a 400 error with message
			$this->reply("No Action Provided", 400);
			//kill script
			exit();
		}

		//check if method for the action exists
		if(!method_exists($this, $this->request->action)){
			//if method doesn't exist, send 400 code and message with reply'
			$this->reply("Action method not found",400);
			//kill script
			exit();
		}
        
		switch($this->request->action){
			case "hello":
				$this->hello($this->request->data);
				break;
			case "getUserState":
				error_log("getUserState has been sent through");
				$data = json_decode($this->request->data);
				$this->getUserState($data->lti_id, $data->user_id);
				break;
			case "setUserState":
				error_log("setUserState has been sent through");
				$request = $this->request;


				$newState = json_decode($request->app_state, true);
				$newState["submitted"] = true;
				$this->setUserState($request->lti_id, $request->user_id, $newState);

				send_grade(1, $this->config, $request->lti_grade_url, $request->result_sourcedid, $request->lti_consumer_key);
				$this->reply($newState,200);

				break;	
			default:
				$this->reply("action switch failed",400);
			break;
		}



	}

    public function hello(){
		$data = json_decode($this->request->data);
		$this->reply("Hello ".$data->name.", I'm PHP :)");
	}

	public function setUserState($lti_id, $user_id, $state){
		$state = json_encode($state);
        date_default_timezone_set('Australia/Brisbane');
        $modified = date('Y-m-d H:i:s');
		if(!$this->checkTableExists("states")){
			$this->db->raw("CREATE TABLE states (
				id INT(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
				user_id TEXT NOT NULL,
				lti_id TEXT NOT NULL,
				state MEDIUMTEXT,
				created DATETIME DEFAULT NULL,
				updated DATETIME DEFAULT NULL
			)");
		}
		$existing = $this->checkStateExists($lti_id, $user_id);
		if(!$existing) {
			$this->db->create('states', array('lti_id'=>$lti_id,'user_id'=>$user_id, 'state'=>$state,'created'=>$modified,'updated'=>$modified));
		} else {
			$this->db->query('UPDATE states SET state = :state WHERE lti_id = :lti_id AND user_id = :user_id', array( 'state' => $state, 'lti_id' => $lti_id, 'user_id' => $user_id ) );
		}
    }

	public function getUserState($lti_id, $user_id){
		
		if(!$this->checkTableExists("states")){
			//$this->reply("Table 'states' for user:".$user_id." in lti:".$lti_id." not found", 404);
			$this->db->raw("CREATE TABLE states (
				id INT(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
				user_id TEXT NOT NULL,
				lti_id TEXT NOT NULL,
				state MEDIUMTEXT,
				created DATETIME DEFAULT NULL,
				updated DATETIME DEFAULT NULL
			)");
		}
        $select = $this->db->query( 'SELECT state FROM states WHERE lti_id = :lti_id AND user_id = :user_id', array( 'lti_id' => $lti_id, 'user_id' => $user_id ) );
        while ( $row = $select->fetch() ) {
           $this->reply($row);
        }
        $this->reply("State in table 'states' for user:".$user_id." in lti:".$lti_id." not found",404);
    }

	private function checkTableExists($tableName){
		$select = $this->db->query("SELECT * 
			FROM information_schema.tables
			WHERE table_schema = :dbname 
				AND table_name = :tablename
			LIMIT 1", array("dbname"=>$this->config["db"]["dbname"], "tablename"=>$tableName));
		if($select->fetch()){
			return true;
		}
		return false;
	}
	private function checkStateExists($lti_id, $user_id){
        $select = $this->db->query( 'SELECT state FROM states WHERE lti_id = :lti_id AND user_id = :user_id', array( 'lti_id' => $lti_id, 'user_id' => $user_id ) );
        while ( $row = $select->fetch() ) {
		   return true;
        }
		return false;
	}


	/**
	 * Prevent unauthenticated access to the backend
	 */
	private function _secureBackend()
	{
		if (!$this->_isAuthenticated()) {
			header("HTTP/1.1 401 Unauthorized");
			exit();
		}
	}

	/**
	 * Check if user is authenticated
	 *
	 * This is just a placeholder. Here you would check the session or similar
	 * to see if the user is logged in and/or authorized to make API calls.
	 */
	private function _isAuthenticated()
	{
		return true;
	}

	/**
	 * Returns JSON data with HTTP status code
	 *
	 * @param  array $data - data to return
	 * @param  int $status - HTTP status code
	 * @return JSON
	 */
	private function reply($data, $status = 200){
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
        header($protocol . ' ' . $status);
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}

	/**
	 * Determines if the logged in user has admin rights
	 *
	 * This is just a placeholder. Here you would check the session or database
	 * to see if the user has admin rights.
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		$this->reply(true);
	}


} //MyApi class end

require_once('../lib/db.php');
require_once('../config.php');
require_once('../lib/OAuth.php');
require_once('../lib/grade.php');

if(isset($config['use_db']) && $config['use_db']) {
	Db::config( 'driver',   'mysql' );
	Db::config( 'host',     $config['db']['hostname'] );
	Db::config( 'database', $config['db']['dbname'] );
	Db::config( 'user',     $config['db']['username'] );
	Db::config( 'password', $config['db']['password'] );
}

$db = Db::instance(); //uncomment and enter db details in config to use database
$MyApi = new MyApi($db, $config);

