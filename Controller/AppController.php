<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

session_start();
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	public $components = array(
		'DebugKit.Toolbar',	// added the debug toolkit
		'Session',	// sessions support
        'Auth'=>array(	// authorization for login and logout redirect
			'authenticate'=>array(
				'Form'=>array(
					'userModel'=>'User',
					'fields'=>array(
						'username'=>'email',
						'password'=>'password'
					),
					'passwordHasher' => 'Blowfish'
				)
			),
			'authorize'=>array('Controller'),
            'loginRedirect'=>array(
            	'controller'=>'users', 
            	'action'=>'index'
            ),
            'logoutRedirect'=>array(
            	'controller'=>'users', 
            	'action'=>'login'
            ),
			'authError'=>'Access Denied.',
			'loginError'=>'Invalid Username or Password entered, please try again.'
    ));
	
	// Allow the login controller
	public function beforeFilter() {
        $this->Auth->allow('login');
    }
	
	public function isAuthorized($user) {
		// To verify the role and give access based on role
		return true;
	}

}
