<?php

/**
 * WikiaLogin Special Page
 * @author Hyun
 * @author Saipetch
 *
 */
class WikiaLoginSpecialController extends WikiaSpecialPageController {

	public function __construct() {
		wfLoadExtensionMessages('WikiaLogin');
		parent::__construct('WikiaLogin', '', false);
	}
	
	public function init() {
		$this->response->addAsset('extensions/wikia/WikiaLogin/js/WikiaLogin.js');
	}
	
	/**
	 * @brief serves standalone login page on GET.  if POSTed, parameters will be required.
	 * @details
	 *   on GET, template will render
	 *   on POST, 
	 *     if login is successful, it will redirect to returnUrl, or default login welcome screen
	 *     if login is not successful, the template will render will error messages, highlighting the errors
	 * @requestParam string username - on POST
	 * @requestParam string password - on POST
	 * @requestParam string keeploggedin [true/false] - on POST
	 * @requestParam string returnUrl - url to return to upon successful login
	 */
	public function index() {
		
	}
	
	/**
	 * @brief renders html version that will be inserted into ajax based login interaction
	 * @details
	 *   on GET, template partial for an ajax element will render
	 */
	public function widget() {
		
	}
	
	/**
	 * @brief logs in a user with given login name and password.  if keeploggedin, sets a cookie.
	 * @details
	 * @requestParam string username
	 * @requestParam string password
	 * @requestParam string keeploggedin [true/false]
	 * @responseParam string result [ok/error/null]
	 * @responseParam string[] errors - error messages
	 */
	public function login() {
		
	}
	
	/**
	 * @brief sends an email to username's address
	 * @details
	 *   if success, send email
	 *   if no email addy for username, set error and msg
	 *   if no username, set error and msg
	 * @requestParam string username
	 * @responseParam string result [ok/noemail/error/null]
	 * @responseParam string msg - message
	 */
	public function emailpassword() {
		
	}

}
