<?php
App::uses('AppModel', 'Model');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class User extends AppModel {
	
	public $hasMany = array(
        'SocialProfile' => array(
            'className' => 'SocialProfile'
        )
    );
    
	public $validate = array(
        'username' => array(
            'nonEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'A username is required',
				'allowEmpty' => false
            ),
			'isAlphabetic' => array(
				'rule'    => array('isAlphabetic'),
				'message' => 'Username can only be letters'
			),
        ),
		'email' => array(
			'required' => array(
				'rule' => array('email', true),    
				'message' => 'Please provide a valid email address.'    
			),
			 'unique' => array(
				'rule'    => array('isUniqueEmail'),
				'message' => 'This email is already in use',
			),
			'between' => array( 
				'rule' => array('between', 6, 60), 
				'message' => 'Usernames must be between 6 to 60 characters'
			)
		),
        'password' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'A password is required'
            ),
			'min_length' => array(
				'rule' => array('minLength', '6'),  
				'message' => 'Password must have a mimimum of 6 characters'
			)
        ),
        'phone' => array(
    //         'nonEmpty' => array(		// Commented to allow social login
    //             'rule' => array('notBlank'),
    //             'message' => 'The phone number is required',
				// 'allowEmpty' => false
    //         ),
			'isNumeric' => array(
				'rule'    => array('isNumeric'),
				'message' => 'Phone have only between 7 and 11 digits'
			)
        ),
        'role' => array(
            'valid' => array(
                'rule' => array('inList', array('admin', 'agent', 'customer')),
                'allowEmpty' => false
            )
        ),
		'password_update' => array(
			'min_length' => array(
				'rule' => array('minLength', '6'),   
				'message' => 'Password must have a mimimum of 6 characters',
				'allowEmpty' => true,
				'required' => false
			)
        )
		
    );

	/**
	 * Before isUniqueEmail
	 * @param array $options
	 * @return boolean
	 */
	function isUniqueEmail($check) {
		$email = $this->find(
			'first',
			array(
				'fields' => array(
					'User.id'
				),
				'conditions' => array(
					'User.email' => $check['email']
				)
			)
		);
		if(!empty($email)){
			return isset($this->data[$this->alias]['id']) && 
				$email['User']['id'] == $this->data[$this->alias]['id'] ? 
				true : false;
		}else{
			return true; 
		}
    }
	
    /**
     * Username must contain alphabetic characters only
     * @param array $check
	 * @return integer or boolean
     */
	public function isAlphabetic($check) {
        $value = array_values($check);
        $value = $value[0];
        return preg_match('/^[a-zA-Z ]*$/', $value);
    }

    /**
     * Phone must contain between 7 and 11 digits only
     * @param array $check
	 * @return integer or boolean
     */
	public function isNumeric($check) {
        $value = array_values($check);
        $value = $value[0];
        return preg_match('/^[\d]{7,11}$/', $value);
    }

    /**
     * Creates a brand new user from a given social profile
     * @param array $incomingProfile
	 * @return array usersmanager user
     */
    public function createFromSocialProfile($incomingProfile){
	    // Check to ensure that we are not using an email that already exists
	    $existingUser = $this->find('first', array(
	        'conditions' => array('email' => $incomingProfile['SocialProfile']['email'])
	    ));
	    if($existingUser){
	        return $existingUser;
	    }
	    // brand new user
	    $socialUser['User']['email'] = $incomingProfile['SocialProfile']['email'];
	    $socialUser['User']['username'] = $incomingProfile['SocialProfile']['user_name'];
	    $socialUser['User']['role'] = 'customer'; // Default role
	    $socialUser['User']['password'] = date('Y-m-d h:i:s'); // Technically this means nothing, but a password it's still necessary for social so let's set a random one like the current time.
	    $socialUser['User']['created'] = date('Y-m-d h:i:s');
	    $socialUser['User']['modified'] = date('Y-m-d h:i:s');
	    $result = $this->save($socialUser);
	    debug($result);
	    $socialUser['User']['id'] = $this->id;
	    return $socialUser;
	}

	/**
     * Retrieves the list of users to export
	 * @return array usersmanager user
     */
    public function exportUsers(){
		$sql = "SELECT id, username, email, phone, role, status, created, modified
			FROM users 
			ORDER BY username ASC";
	    return $this->query($sql);
	}

	/**
	 * Before Save
	 * @param array $options
	 * @return boolean
	 */
	public function beforeSave($options = array()) {
		// hash the password
		if (isset($this->data[$this->alias]['password'])) {
	        $passwordHasher = new BlowfishPasswordHasher();
	        $this->data[$this->alias]['password'] = $passwordHasher->hash(
	            $this->data[$this->alias]['password']
	        );
	    }
		// if new password passed, hash it
		if (isset($this->data[$this->alias]['password_update'])) {
		    $passwordHasher = new BlowfishPasswordHasher();
		    $this->data[$this->alias]['password'] = $passwordHasher->hash(
		        $this->data[$this->alias]['password_update']
		    );
		}
		return true;
	}

}

?>