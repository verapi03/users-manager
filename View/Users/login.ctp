<div class="users form">
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Please enter your email and password'); ?></legend>
        <?php 
        echo $this->Form->input('email');
        echo $this->Form->input('password');
    	?>
    </fieldset>
<?php echo $this->Form->end(__('Login')); ?>
<br/>
<h2>... OR...</h2>
<br/>
<?php
echo $this->Html->image("login-facebook.jpg", array(
    "alt" => "Signin with Facebook",
    'url' => array('action'=>'social_login', 'Facebook')
));
?>
</div>
<?php
 echo $this->Html->link( "Sign Up Here!", array('action'=>'signup') ); 
?>