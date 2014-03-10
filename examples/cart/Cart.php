<?php
/**
 * Checkfront Sample Code: Browse inventory & create booking.
 *
 * This is sample code is for demonstration only and should not be used in production
 * without modifcation.  It does not adequtly secure your OAuth tokens.
 *
 * see: 
 * 
 * API Documenation:  http://www.checkfront.com/developers/api/
 * API Error Codes:  http://www.checkfront.com/developers/api-error
 * PHP SDK - https://github.com/Checkfront/PHP-SDK
 * CQL Documenation: http://www.checkfront.com/developers/api-cql/
 *
 */

/*
 * @access public
 * @package Checkfront
 */

include('../../lib/CheckfrontAPI.php');

class Checkfront extends CheckfrontAPI {

	public $tmp_file = '.checkfront_oauth';

	public function __construct($data) {
		parent::__construct($data,session_id());
	}

	/* DUMMY Data store.  This sample stores oauth tokens in a text file...
	 * This is NOT reccomened in production.  Ideally, this would be in an encryped 
	 * database or other secure source.  Never expose the client_secret or access / refresh
	 * tokens.
	 *
	 * store() is called from the parent::CheckfrontAPI when fetching or setting access tokens.  
	 *
	 * When an array is passed, the store should save and return the access tokens.
	 * When no array is passed, it should fetch and return the stored access tokens.
	 *
	 * param array $data ( access_token, refresh_token, expire_token )
	 * return array
	 */
	final protected function store($data=array()) {
		$tmp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR. $this->tmp_file;
		if(count($data)  ) {
			file_put_contents($tmp_file,json_encode($data,true));
		} elseif(is_file($tmp_file)) {
			$data = json_decode(trim(file_get_contents($tmp_file)),true);
		}
		return $data;
	}

	public function session($session_id,$data=array()) {
		session_id($session_id);
		if(!empty($data)) $_SESSION = $data;
	}
}

/* 
 * You need to create a new application in your Checkfront Account under
Manage / Extend / Api and supply the details below. 

This example bybasses the oauth authorization redirect by supplying "oob" 
(Out Of Bounds) as the redirect_uri, and by generating the access and 
refresh tokens from within Checkfront. 

For more infomration on your endpoints see: 
http://www.checkfront.com/developers/api/#endpoints
*/

// a general class that wraps the api along with some custom calls
class Booking {

	public $cart = array();
	public $session = array();

	function __construct() {
		// apply a session_id to the request if one is specified
		if (!empty($_GET['cart_id'])) session_id($_GET['cart_id']);
		session_start();
		// create api connection to Checkfront
		// you can generate a token pair under Manage / Developer in your account
		$this->Checkfront = new Checkfront(
			array(
				'host' => 'your-company.checkfront.com',
				'auth_type' => 'token',
				'api_key' => '',
				'api_secret' => '',
				'account_id' => 'off',
			)
		);

		// init shopping cart
		$this->cart();
	}

	// fetch items from inventory based on date
	public function query_inventory($data) {
		$response = $this->Checkfront->get('item',array('start_date'=>$data['start_date'],'end_date'=>$data['end_date']));
		return $response['items'];
	}

	// add slips to the booking session
	public function set($slips=array()) {
		$response = $this->Checkfront->post('booking/session',array('slip'=>$slips));
		$this->Checkfront->set_session($response['booking']['session']['id'], $response['booking']['session']);
		$this->cart();
	}

	// get the booking form fields required to make a booking
	public function form() {
		$response = $this->Checkfront->get('booking/form');
		return $response['booking_form_ui'];
	}

	// get cart session
	public function cart() {
		if(!empty($_SESSION)) {
			$response = $this->Checkfront->get('booking/session');
			if(!empty($response['booking']['session']['item'])) {
				foreach($response['booking']['session']['item']  as $line_id => $data) {
					// store for later
					$this->cart[$line_id] = $data;
				}
			}
			$this->Checkfront->set_session($response['booking']['session']['id'], $response['booking']['session']);
		}
	}

	// create a booking using the session and the posted form fields
	public function create($form) {
		$form['session_id'] = session_id();
		if($response = $this->Checkfront->post('booking/create',array('form'=>$form))) {
			return $response;
		}
	}

	// clear the current remote session
	public function clear() {
		$response = $this->Checkfront->post('booking/session/clear');
		session_destroy();
	}	
}
