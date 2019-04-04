<?php
/**
 * Checkfront SDK 
 * PHP 5 
 *
 * @package     CheckfrontAPI
 * @author      Checkfront <code@checkfront.com>
 * @copyright   2008-2014 Checkfront Inc 
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 * @link        http://www.checkfront.com/developers/api/
 * @link        https://github.com/Checkfront/PHP-SDK
 *
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * o Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote
 *   products derived from this software without specific prior written
 *   permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */


/*
 * @access public
 * @package Checkfront
*/
class CheckfrontAPI {

	protected $sdk_version = '1.4';
	protected $api_version = '3.0';

	public $error = array();
	private $api_timeout = '30';

	// change to the name of your app / integration (shows in log and invoice footer).  maxlength=15
	private $app_id = 'API'; 

	// checkfront host
	private $host;

	// use oauth2 or token
	private $auth_type = 'oauth2';

	// oauth2 only:
	public $consumer_key;
	private $consumer_secret;
	private $access_token;
	private $refresh_token;

	// token auth only:
	private $api_key;
	private $api_secret;


	// set to 'off' to act on behalf of a client / public booking,
	// or override the autheticated user with an integer (account_id).
	// defaults to authenticated user.
	private $account_id;


	// over-ride the ip address of the API call.  Set this if you wish to track
	// another API when creating or updating a booking.
	private $client_ip;
	private $session_id;


	// use to store API keys, ideally via an extended class.  Not used for token pair auth
	protected function store($data=array()) {
	}

	// use to store session if needed for interactive integrations
	public function session($session_id,$data=array()) {
	}

	function __construct($config=array(),$session_id='') {

		$this->host = $config['host'];
		$this->oauth_url = "https://{$this->host}/oauth";
		$this->api_url = "https://{$this->host}/api/{$this->api_version}";

		if(isset($config['app_id'])) $this->app_id = $config['app_id'];

		$this->client_ip= isset($config['client_ip']) ? $config['client_ip'] : '';
		$this->session_id = isset($session_id) ? $session_id : '';
		$this->account_id= isset($config['account_id']) ? $config['account_id']: '';

		if($config['auth_type'] == 'token') {
			$this->api_key = isset($config['api_key']) ? $config['api_key'] : '';
			$this->api_secret = isset($config['api_secret']) ? $config['api_secret'] : '';
			$this->auth_type = 'token';
		} else {
			$this->auth_type = 'oauth2';
			$this->consumer_key = isset($config['consumer_key']) ? $config['consumer_key'] : '';
			$this->consumer_secret = isset($config['consumer_secret']) ? $config['consumer_secret'] : '';
			$this->redirect_uri = isset($config['redirect_uri']) ? $config['redirect_uri'] : '';
			$this->tokens();
		}
	}

	/**
	 * Check and refresh access token if needed.
	 *
	 * @return bool 
    */
	private function init() {

		// No OAuth for tokens
		if($this->auth_type == 'token') return;

		if(isset($this->refresh_token)) {
			if(!$this->access_token or $this->expire_token < time()) {
				$this->refresh_token();
			}
		}
	}

	
	/**
	 * API call via curl
	 *
	 * @param string $url 
	 * @param array $data post / get data
	 *
	 * @return array json parsed response array
	 */
	final private function call($url,$data=array(),$type='') {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Checkfront PHP/SDK {$this->sdk_version} ({$this->app_id})");
		curl_setopt($ch, CURLOPT_HEADER, 0);

		// set custom headers
		$headers = array('Accept: application/json');

		if($this->client_ip) {
			$headers[] = "X-Forwarded-For: {$this->client_ip}";
		} else {
			$headers[] = "X-Forwarded-For: {$_SERVER['REMOTE_ADDR']}";
		}

		if($this->account_id) {
			$headers[] = "X-On-Behalf: {$this->account_id}";
		}

		if($this->auth_type == 'token') {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . $this->api_secret);
		} else {
			if($this->access_token) {
				$headers[] = "Authorization: BEARER {$this->access_token}";
			}
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// pass session id
		// @see session_create()
		if($this->session_id) {
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIE, "session_id={$this->session_id}");
		}


		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->api_timeout);

		if($data) {
			curl_setopt($ch, CURLOPT_POST, true);
			if(is_array($data)) {
				// needs to encode to support assocative arrays
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
		} else {
			curl_setopt($ch, CURLOPT_HTTPGET, true);
		}

		/**
		 * If you get the error "SSL certificate problem: unable to get local issuer certificate",
		 * download the following file to this directory and uncomment the option below:
		 * http://curl.haxx.se/ca/cacert.pem
		 */
		//curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

		if($response = curl_exec($ch)) {
			$response = json_decode($response,true);
			if($error = json_last_error()) {
				$this->error = array('json'=>$error);
			} else {
				return $response;
			}
		} else {
			$this->error = array('curl'=>curl_error($ch));
		}
	}

	/**
	 * Set the session
	 *
	 * @param string session_id
	 * @param array $data session
	 *
	 * @return bool
	*/
	final public function set_session($session_id,$data=array()) {
		$this->session_id = $session_id;
		return $this->session($session_id,$data);
	}

	/**
	 * API request
	 *
	 * @param string $url 
	 * @param array $data post / get data
	 *
	 * @return array json parsed response array
	 */
	final function api($path,$data=array()) {

		$this->init();

		$url = $this->api_url . '/' . $path;
		if($response = $this->call($url,$data)) {
			if(!empty($response['booking']['session']['id'])) {
				$this->session($response['booking']['session']['id']);
			}
			return $response;
		} else {
			return false;
		}
	}


	/**
	 * Authorize request
	 *
	 * @param bool $redirect automatically send the redirect header
	 *
	 * @return mixed Returns if $redirect is true returns bool, otherwise returns the authorization URL
	 * @link http://www.checkfront.com/developers/api/#oauth
	 */
	final public function authorize($redirect=1) {
		$args = array(
			'type'=>'web_server',
			'client_id'=>$this->consumer_key,
			'redirect_uri'=>$this->oauth_redirect_uri,
			'response_type'=>'code',
		);
		$url = $this->oauth_url . '/?' . http_build_query($args);

		if($redirect) {
			return header("Location: {$url}");
		} else {
			return $url;
		}
	}


	/**
	 * Fetch access token
	 * @param string $code returned from Checkfront::authorize()
	 * @return mixed 
	 */
	final public function fetch_token($code) {

		$data = array(
			'type'=>'web_server',
			'client_id'=>$this->consumer_key,
			'grant_type' => 'authorization_code',
			'client_secret' => $this->consumer_secret,
			'redirect_uri'=>$this->redirect_uri,
			'code'=>$code,
		);


		$url = $this->oauth_url . '/token/';
		if($tokens = $this->call($url,$data)) {
			if($tokens['error']) {
				return false;
			} else {
				$this->tokens($tokens);
				return true;
			}
		}
	}

	/**
	 * Refresh access token
	 * @param string $code returned from Checkfront::authorize()
	 * @return mixed 
	 */
	final private function refresh_token() {

		$data = array(
			'type'=>'web_server',
			'client_id'=>$this->consumer_key,
			'grant_type' => 'refresh_token',
			'client_secret' => $this->consumer_secret,
			'redirect_uri'=>$this->redirect_uri,
			'refresh_token'=>$this->refresh_token,
		);

		$url = $this->oauth_url . '/token/';
		if($tokens = $this->call($url,$data)) {

			if($tokens['error']) {
				return false;
			} else {
				$this->tokens($tokens);
				return true;
			}
		}
	}


	/**
	 * Get expire token datetime
	 *
	 * @param integer $time seconds to expore
	 *
	 * @return integer unix date - expire dirft. 
	 */
	private function expire_token($time) {
		return time() + $time - $this->expire_drift;
	}

	/**
	 * Set access token  
	 *
	 * @param array $data Token data (access_token,refresh_token,expire_token)
	 *
	 * @return mixed 
	 */
	private function tokens($data=array()) {

		if($data) {
			if($data['expires_in']) {
				$data['expire_token'] = $this->expire_token($data['expires_in']);
			}
			$this->store(
				array(
					'refresh_token'=>$data['refresh_token'],
					'access_token'=>$data['access_token'],
					'expire_token'=>$data['expire_token'],
					'updated'=>time()
				)
			);
		} else {
			$data = $this->store();
		}

		if(isset($data['access_token'])) $this->access_token  = $data['access_token'];
		if(isset($data['refresh_token'])) $this->refresh_token = $data['refresh_token'];
		if(isset($data['expire_token'])) $this->expire_token = $data['expire_token'];
	}


	/**
	 * API GET request
	 *
	 * @param string $url 
	 * @param array $ags get data
	 *
	 * @return array json parsed response array
	 */
	final public function get($path, $data=array()) {
		if($data) $path .= '/?'  . http_build_query($data);
		if($response = $this->api($path)) {
			return $response;
		} else {
			return false;
		}
	}


	/**
	 * API PUT request
	 *
	 * @param string $url 
	 * @param array $ags put data
	 *
	 * @return array json parsed response array
	 */
	final public function put($path,$data) {
		return $this->api($path,$data,'put');
	}


	/**
	 * API DELETE request
	 *
	 * @param string $url 
	 *
	 * @return array json parsed response array
	 */
	final public function delete($path,$data) {
		return $this->api($path,$data,'delete');
	}


	/**
	 * API POST
	 *
	 * @param string $path uri path
	 * @param array $data post data
	 *
	 * @return mixed 
	 */
	final public function post($path,$data) {
		return $this->api($path,$data);
	}


	public function session_clear() {
		$this->session_id = 0; 
	}
}
?>
