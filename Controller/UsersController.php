<?php
App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController {

	var $uses = array('User','SocialProfile');
	public $components = array('Hybridauth');

	public $paginate = array(
        'limit' => 10,
        'conditions' => array('status' => '1'),
    	'order' => array('User.username' => 'asc' ) 
    );
	/**
	 * Function to only allow the login, signup, etc, to be authorized in any controller. All other 
	 * actions will only be accessible after the user is logged-in.
	 */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('login','signup','verify','social_login','social_endpoint'); 
    }
	
	/**
	 * This grants access permission upon credentials.
	 */
	public function login() {
		//If already logged-in, redirect
		if ($this->Session->check('Auth.User')) {
			$this->redirect(array('action' => 'index'));		
		}
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				if($this->Auth->user('status')){
					$this->Session->setFlash(__('Welcome, '. $this->Auth->user('username')));
					$this->redirect($this->Auth->redirectUrl());
				}else{
					$this->Auth->logout();
					$this->Session->setFlash(__('You are entering wrong credentials or you have not activate your user or your user was removed by an admin.'));
				}
			} else {
				$this->Session->setFlash(__('Invalid email or password'));
			}
		} 
	}

	/**
	 * Logout action.
	 */
	public function logout() {
		$this->Hybridauth->logout();
		$this->redirect($this->Auth->logout());
	}

	/**
	 * Alternate way that users can login to the application. This is not exclusive for fb use 
	 * only, it can be used to allow login from other platforms.
	 */
	public function social_login($provider) {
		if( $this->Hybridauth->connect($provider) ){
			$this->_successfulHybridauth($provider,$this->Hybridauth->user_profile);
        }else{
            // error
			$this->Session->setFlash($this->Hybridauth->error);
			$this->redirect($this->Auth->loginAction);
        }
	}
 	
 	/**
 	 * Acts as a wrapper class for the HybridAuthComponent’s endpoint function. It is like
 	 * a link that facebook redirects to after they have verified your key and secret.
 	 */
	public function social_endpoint($provider = null) {
		$this->Hybridauth->processEndpoint();
	}
	
	/**
	 * Completes the facebook login process if the login in the facebook side is successful 
	 * and also informs the Auth component to allow the user to get in.
	 */
	private function _successfulHybridauth($provider, $incomingProfile){
		// Check if the user is already authenticated using this provider before
		$this->SocialProfile->recursive = -1;
		$existingProfile = $this->SocialProfile->find('first', array(
			'conditions' => array(
				'social_network_id' => $incomingProfile['SocialProfile']['social_network_id'], 
				'social_network_name' => $provider
			)
		));
		if ($existingProfile) {
			// If an existing profile exits, we set the user as connected and log them in
			$user = $this->User->find('first', array(
				'conditions' => array('id' => $existingProfile['SocialProfile']['user_id'])
			));
			$this->_doSocialLogin($user,true);
		} else {		
			// New profile.
			if ($this->Auth->loggedIn()) {
				// User is already logged-in , attach profile to logged in user.
				// Create social profile linked to current user.
				$incomingProfile['SocialProfile']['user_id'] = $this->Auth->user('id');
				$this->SocialProfile->save($incomingProfile);
				$this->Session->setFlash('Your ' . $incomingProfile['SocialProfile']['social_network_name'] . ' account is now linked to your account.');
				$this->redirect($this->Auth->redirectUrl());
			} else {
				// no-one logged and no profile, must be a registration.
				debug($incomingProfile);
				$user = $this->User->createFromSocialProfile($incomingProfile);
				debug($user);
				$incomingProfile['SocialProfile']['user_id'] = $user['User']['id'];
				debug($incomingProfile);
				$this->SocialProfile->save($incomingProfile);
				// log in with the newly created user
				$this->_doSocialLogin($user);
			}
		}	
	}
	
	/**
	 * This method takes in the user object authenticated in facebook and tries that the 
	 * Auth component to validate it to let the user successfully logged-in.
	 */
	private function _doSocialLogin($user, $returning = false) {
		// CakePHP’s Auth component alternative login function
		if ($this->Auth->login($user['User'])) {
			if($returning){
				$this->Session->setFlash(__('Welcome back, '. $this->Auth->user('username')));
			} else {
				$this->Session->setFlash(__('Welcome to this awesome community, '. $this->Auth->user('username')));
			}
			$this->redirect($this->Auth->loginRedirect);
		} else {
			$this->Session->setFlash(__('Unknown Error could not verify the user: '. $this->Auth->user('username')));
		}
	}

	/**
     * Dashboard that retrieves from the model the info of all users or a single user 
     * and send it to the view.
     */
    public function index() {
    	if ($this->Auth->user('role') != 'customer') {
	    	$this->paginate = array(
				'limit' => 6,
				'order' => array('User.username' => 'asc' )
			);
			$users = $this->paginate('User');
		} else {
			$users[] = array('User'=>$this->User->findById($this->Auth->user('id'))['User']);
		}
		$this->set(compact('users'));
    }

    /**
     * This function allows a new user to sing up in the system and sends the verification email.
     */
    public function signup() {
    	if ($this->request->is('post')) {
			$this->User->create();
			$hash = sha1($this->data['User']['username'].rand(0,1000));	// Creates token
			$this->request->data['User']['token'] = $hash;
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('Cool, you are one of us now, go check your email and comeback.'));

				$msg = "Click on the the link below to complete your registration on usersmanager.\n\n".
				"href=http://ec2-52-37-8-240.us-west-2.compute.amazonaws.com/users/verify/t:".$hash."/i:"
				.$this->User->id."\n\nIf clicking the link doesn't seem to work, you can copy and paste ".
				"the link into your browser's address window, or retype it there.";
				
				$Email = new CakeEmail('default');
				$Email->from(array('raul.andres.vp@gmail.com' => 'usersmanager'));
				$Email->to($this->data['User']['email']);
				$Email->subject(__('Confirm Registration for usersmanager'));
				$result = $Email->send($msg);

				$this->redirect(array('action' => 'login'));
			} else {
				$this->Session->setFlash(__('An error occurred. Please, try again.'));
			}	
        }
    }

    /**
     * This function compares a token and an ID sent from an email against the token
     * saved in the DB in order to grant access permision to a new user. 
     */
    public function verify() {
    	// debug($this->Auth->login());
		if (!empty($this->passedArgs['i']) && !empty($this->passedArgs['t'])){
			$user = $this->User->findById($this->passedArgs['i']);
			if (!$user['User']['status']) {
				if ($user['User']['token'] == $this->passedArgs['t']) {
					// $user['User']['status'] = 1;
					// $this->User->save($user);
					$data = array('id' => $this->passedArgs['i'], 'status' => 1);
					$this->User->save($data);
					$this->Session->setFlash('Your registration is complete, go ahead and sign in.');
				} else {
					$this->Session->setFlash('Your registration failed, forward the email to the admin at raul.andres.vp@gmail.com');
				}
			} else {
				$this->Session->setFlash('Token has alredy been used, go ahead and sign in.');
			}
		} else {
			$this->Session->setFlash('Token corrupted, forward the email to the admin at raul.andres.vp@gmail.com');
		}
		$this->redirect(array('action' => 'login'));
	}

	/**
	 * Allows the admin to add new users to the system. It is available when 
	 * the admin is logged-in.
	 */
    public function create() {
        if ($this->request->is('post')) {
			$this->User->create();
			$this->request->data['User']['status'] = 1;
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been created'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be created. Try again.'));
			}	
        }
    }

    /**
     * Method to edit user info.
     */
    public function edit($id = null) {
	    if (!$id) {
			$this->Session->setFlash('Please provide a user id');
			$this->redirect(array('action'=>'index'));
		}
		$user = $this->User->findById($id);
		if (!$user) {
			$this->Session->setFlash('Invalid User ID Provided');
			$this->redirect(array('action'=>'index'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->User->id = $id;
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The register has been updated'));
				$this->redirect(array('action' => 'index'));
			}else{
				$this->Session->setFlash(__('Unable to update'));
			}
		}
		if (!$this->request->data) {
			$this->request->data = $user;
		}
    }

    /**
	 * Method to turn a user’s status to inactive by an Admin. This is an alternative to
	 * the method delete.
	 */
    public function inactivate($id = null) {
		if (!$id) {
			$this->Session->setFlash('Please provide a user id');
			$this->redirect(array('action'=>'index'));
		}
        $this->User->id = $id;
        if (!$this->User->exists()) {
            $this->Session->setFlash('Invalid user ID');
			$this->redirect(array('action'=>'index'));
        }
        if ($this->User->saveField('status', 0)) {
            $this->Session->setFlash(__('User inactivated'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Unable inactivate user'));
        $this->redirect(array('action' => 'index'));
    }
	
	/**
	 * Method to turn a user’s status to active after they have been inactivated by an Admin.
	 */
	public function activate($id = null) {
		if (!$id) {
			$this->Session->setFlash('Please provide a user id');
			$this->redirect(array('action'=>'index'));
		}
        $this->User->id = $id;
        if (!$this->User->exists()) {
            $this->Session->setFlash('Invalid user ID');
			$this->redirect(array('action'=>'index'));
        }
        if ($this->User->saveField('status', 1)) {
            $this->Session->setFlash(__('User activated'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Unable to activate user'));
        $this->redirect(array('action' => 'index'));
    }

    /**
     * Deletes a user from the DB. Exclusive for admin usage.
     */
    public function delete($id) {
	    if ($this->User->delete($id)) {
	        $this->Session->setFlash(__('User deleted succesfully'));
	    } else {
	        $this->Session->setFlash(__('An error ocurred deleting the user'));
	    }
	    return $this->redirect(array('action' => 'index'));
	}

	/**
     * Exports the list of users from the DB. Exclusive for admin usage.
     */
    public function export() {
    	App::import('Vendor', 'PHPExcel/Classes/PHPExcel.php');
    	$users = $this->User->exportUsers();
		// debug($users);
		if (count($users) > 0) {
			$objPHPExcel = new PHPExcel();
		  
			//Excel information
			$objPHPExcel->getProperties()
				->setCreator("Andres Vera")
				->setLastModifiedBy("Andres Vera")
				->setTitle("Users List")
				->setSubject("Users List")
				->setDescription("Users Document generated with PHPExcel")
				->setKeywords("andres phpexcel")
				->setCategory("users");

			$i = 1;    
			foreach ($users as $user) {
				if ($i == 1) {
					$letter = "A";
					foreach ($user['users'] as $field_name => $field_value) {
						$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($letter.$i, $field_name);
						$letter++;	
					}
				} else {
					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("A".$i, $user['users']['id'])
						->setCellValue("B".$i, $user['users']['username'])
						->setCellValue("C".$i, $user['users']['email'])
						->setCellValue("D".$i, $user['users']['phone'])
						->setCellValue("E".$i, $user['users']['role'])
						->setCellValue("F".$i, $user['users']['status'])
						->setCellValue("G".$i, $user['users']['created'])
						->setCellValue("H".$i, $user['users']['modified']);
				}
				$i++;
			}
		}

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="users-manager.xlsx"');
		header('Cache-Control: max-age=0');
		 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
		$objWriter->save('php://output');

		// $this->Session->setFlash(__('Check out your download folder'));
		return $this->redirect(array('action' => 'index'));
	}

}
?>