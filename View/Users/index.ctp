<div class="users form">
<h1>Users</h1>
<table>
	<?php $session_user = $this->Session->read('Auth.User'); ?>	
    <thead>
		<tr>
			<th style="text-align: center;"><?php echo $this->Paginator->sort('username', 'Name');?>  </th>
			<th style="text-align: center;"><?php echo $this->Paginator->sort('email', 'Email');?></th>
			<th style="text-align: center;"><?php echo $this->Paginator->sort('phone', 'Phone');?></th>
			<th style="text-align: center;"><?php echo $this->Paginator->sort('role','Role');?></th>
			<th style="text-align: center;"><?php echo $this->Paginator->sort('modified','Last Update');?></th>
			<?php if ($session_user['role'] == 'admin'): echo '<th>Actions</th>' ?><?php endif; ?>
		</tr>
	</thead>
	<tbody>	
		<?php $count=0; ?>
		<?php foreach($users as $user): ?>				
			<?php $count ++;?>
			<?php if($count % 2): echo '<tr>'; else: echo '<tr class="zebra">' ?>
			<?php endif; ?>
			<td style="text-align: center;">
				<?php 
					if ($user['User']['id'] == $session_user['id']) {
						echo $this->Html->link( $user['User']['username'], 
							array('action'=>'edit', $user['User']['id']),
							array('escape'=>false) 
						);	
					} else {
						echo $user['User']['username'];
					}
				?>
			</td>
			<td style="text-align: center;"><?php echo $user['User']['email']; ?></td>
			<td style="text-align: center;"><?php echo $user['User']['phone']; ?></td>
			<td style="text-align: center;"><?php echo $user['User']['role']; ?></td>
			<td style="text-align: center;"><?php echo $this->Time->niceShort($user['User']['modified']); ?></td>
			<?php if($session_user['role'] == 'admin'): ?>
			<td > 
			<?php
				echo $this->Html->link("Edit |", array('action'=>'edit', $user['User']['id']) );
				if( $user['User']['status'] != 0){ 
					echo $this->Html->link(" Inactivate", array('action'=>'inactivate', $user['User']['id']));
				} else {
					echo $this->Html->link(" Activate", array('action'=>'activate', $user['User']['id']));
				}
				echo $this->Html->link("| Delete", array('action'=>'delete', $user['User']['id']));
			?>
			</td>
			<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		<?php unset($user); ?>
	</tbody>
</table>
<?php if($session_user['role'] != 'customer'): ?>
	<?php echo $this->Paginator->prev('<< ' . __('previous ', true), array(), null, array('class'=>'disabled'));?>
	<?php echo $this->Paginator->numbers(array('class' => 'numbers'));?>
	<?php echo $this->Paginator->next(__(' next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
<?php endif; ?>
</div>
<?php if($session_user['role'] == 'admin'): ?>
	<?php echo $this->Html->link( "Create A New User", array('action'=>'create'),array('escape' => false) ); ?>
	<br>
<?php endif; ?>
<?php echo $this->Html->link( "Logout", array('action'=>'logout') ); ?>
