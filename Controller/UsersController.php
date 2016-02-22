<?php
class UsersController extends AppController {

	var $uses = array('User','SocialProfile');
	public $components = array('Hybridauth');

	public $paginate = array(
        'limit' => 10,
        'conditions' => array('status' => '1'),
    	'order' => array('User.username' => 'asc' ) 
    );
	
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('login','signup','social_login','social_endpoint'); 
    }
	
	public function login() {
		//If already logged-in, redirect
		if ($this->Session->check('Auth.User')) {
			$this->redirect(array('action' => 'index'));		
		}
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				$this->Session->setFlash(__('Welcome, '. $this->Auth->user('username')));
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Session->setFlash(__('Invalid email or password'));
			}
		} 
	}

	public function logout() {
		$this->Hybridauth->logout();
		$this->redirect($this->Auth->logout());
	}

	/* social login functionality */
	public function social_login($provider) {
		if( $this->Hybridauth->connect($provider) ){
			$this->_successfulHybridauth($provider,$this->Hybridauth->user_profile);
        }else{
            // error
			$this->Session->setFlash($this->Hybridauth->error);
			$this->redirect($this->Auth->loginAction);
        }
	}

	public function social_endpoint($provider = null) {
		$this->Hybridauth->processEndpoint();
	}
	
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


    public function signup() {
        if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('Cool, you are one of us now, go check your email and comeback.'));
				$this->redirect(array('action' => 'login'));
			} else {
				$this->Session->setFlash(__('An error occurred. Please, try again.'));
			}	
        }
    }

    public function create() {
        if ($this->request->is('post')) {
				
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been created'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be created. Try again.'));
			}	
        }
    }

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
				$this->redirect(array('action' => 'edit', $id));
			}else{
				$this->Session->setFlash(__('Unable to update'));
			}
		}
		if (!$this->request->data) {
			$this->request->data = $user;
		}
    }

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

    public function delete($id) {
	    if ($this->User->delete($id)) {
	        $this->Session->setFlash(__('User deleted succesfully'));
	    } else {
	        $this->Session->setFlash(__('An error ocurred deleting the user'));
	    }
	    return $this->redirect(array('action' => 'index'));
	}

}
?>