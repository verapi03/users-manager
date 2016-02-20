<!-- app/View/Users/signup.ctp -->
<div class="users form">

<?php echo $this->Form->create('User');?>
    <fieldset>
        <legend><?php echo __('Sign Up'); ?></legend>
        <?php 
	        echo $this->Form->input('username');
	        echo $this->Form->input('phone');
			echo $this->Form->input('email');
	        echo $this->Form->input('password');
			echo $this->Form->submit('Submit', array(
				'class' => 'form-submit',  
				'title' => 'Click here to submit your info') 
			); 
		?>
    </fieldset>
<?php echo $this->Form->end(); ?>
</div>
<?php 
echo $this->Html->link("Return to Login Screen", array('action'=>'login')); 
?>