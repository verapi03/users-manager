<!-- app/View/Users/add.ctp -->
<div class="users form">

<?php echo $this->Form->create('User');?>
    <fieldset>
        <legend><?php echo __('Create User'); ?></legend>
        <?php 
            echo $this->Form->input('username');
            echo $this->Form->input('phone');
            echo $this->Form->input('email');
            echo $this->Form->input('password');
            echo $this->Form->input('role', array(
                'options' => array('customer' => 'Customer', 'agent'=>'Agent', 'admin'=>'Admin')
            ));
            echo $this->Form->submit('Submit', array(
                'class' => 'form-submit',  
                'title' => 'Click here to create the user') 
            ); 
        ?>
    </fieldset>
<?php echo $this->Form->end(); ?>
</div>
<?php 
echo $this->Html->link( "Return to Dashboard",   array('action'=>'index') ); 
echo "<br>";
echo $this->Html->link( "Logout",   array('action'=>'logout') ); 
?>