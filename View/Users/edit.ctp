<!-- app/View/Users/add.ctp -->
<div class="users form">
<?php $session_user = $this->Session->read('Auth.User'); ?>	
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Edit User'); ?></legend>
        <?php 
			echo $this->Form->hidden('id', array(
				'value' => $this->data['User']['id']
			));
			echo $this->Form->input('username');
			echo $this->Form->input('email', array(
				'readonly'=>'readonly', 
				'label'=>'Emails can not be changed!'
			));
			echo $this->Form->input('phone');
			if ($session_user['id'] == $this->data['User']['id']){
				echo $this->Form->input('password_update', array(
					'label'=>'New Password (leave empty if you do not want to change)', 
					'maxLength'=>255, 
					'type'=>'password',
					'required'=>0
				));
		    }
	        if ($session_user['role'] == 'admin' && 
	        	isset($this->data['User']['role']) && 
	        	$this->data['User']['role'] != 'admin') {
    			echo $this->Form->input('role', array(
    	            'options'=>array('customer'=>'Customer', 'agent'=>'Agent', 'admin'=>'Admin')
    	        ));
    		}
			echo $this->Form->submit('Edit User', array(
				'class'=>'form-submit', 
				'title'=>'Click to submit the info'
			)); 
		?>
    </fieldset>
<?php echo $this->Form->end(); ?>
</div>
<?php echo $this->Html->link( "Return to Dashboard", array('action'=>'index') ); ?>
<br>
<?php if($session_user['role'] == 'admin'): ?>
	<?php echo $this->Html->link( "Create A New User", array('action'=>'create'),array('escape' => false) ); ?>
	<br>
<?php endif; ?>
<?php echo $this->Html->link( "Logout", array('action'=>'logout') ); ?>