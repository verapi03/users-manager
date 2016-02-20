<!-- app/View/Users/add.ctp -->
<div class="users form">
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
	        echo $this->Form->input('password_update', array(
	        	'label'=>'New Password (leave empty if you do not want to change)', 
	        	'maxLength'=>255, 
	        	'type'=>'password',
	        	'required'=>0
	        ));
			echo $this->Form->input('role', array(
	            'options'=>array('customer'=>'Customer', 'agent'=>'Agent')
	        ));
			echo $this->Form->submit('Edit User', array(
				'class'=>'form-submit', 
				'title'=>'Click to submit the info'
			)); 
		?>
    </fieldset>
<?php echo $this->Form->end(); ?>
</div>
<?php 
echo $this->Html->link( "Return to Dashboard",   array('action'=>'index') ); 
?>
<br/>
<?php 
echo $this->Html->link( "Logout",   array('action'=>'logout') ); 
?>