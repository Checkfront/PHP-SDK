<?php
/**
 * Checkfront Sample Code: Console based CQL query
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

ini_set('display_errors','On');
include('../lib/CheckfrontAPI.php');

class Checkfront extends CheckfrontAPI {

	public $tmp_file = '.checkfront_oauth';

	public function __construct($data) {
		parent::__construct($data);
		session_start();
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
		$_SESSION['checkfront']['session_id'] = $session_id;
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

$Checkfront = new Checkfront(
    array(
        'host' => 'lahaina.checkfront.com',
        'consumer_key' => '201846978350778b0c37f36',
        'consumer_secret' => '192649edd9f4446b7ccb447a931bf45dd6ecf751',
        'redirect_uri' => 'http://dev.lahaina-accommodations.com/checkfront/PHP-SDK/examples/db-query-cql.php', // Enter redirect url, or oob
        'access_token' => '',
    )
);



if($data = $Checkfront->cql('select * from country')) {
    print_r($data);
} elseif($Checkfront->error) {
    print "Error: \n" . var_export($Checkfront->error,true);
}
?>

<pre>
<?
/* Get item details */
$data = $Checkfront->get('item/2');
print_r($data);

/* Get items rates and availbility*/
$data = $Checkfront->get('item',array(
                              'start_date'=>date('Y-m-d'),
                              'end_date'=>date('Y-m-d',strtotime('+3 days'))
                             )
                );
print_r($data);

?>
<pre>