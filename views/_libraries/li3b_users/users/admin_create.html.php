<?=$this->html->script('/li3b_users/js/bootstrapUserValidation', array('inline' => false)); ?>
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<h2 id="page-heading">Create New User</h2>
		</div>
		<?=$this->BlackprintForm->create($document, array('id' => 'user-update-form', 'class' => 'form-horizontal', 'role' => 'form', 'onSubmit' => 'return submitCheck();')); ?>
			<?=$this->security->requestToken(); ?>
				<div class="row">
					<?=$this->BlackprintForm->field('role', array('type' => 'select', 'options' => $roles, 'label' => 'Role', 'size' => '6')); ?>
				</div>

				<div class="row">	
					<?=$this->BlackprintForm->field('firstName', array('label' => 'First Name', 'size' => '6')); ?>
				</div>
				<div class="row">	
					<?=$this->BlackprintForm->field('lastName', array('label' => 'Last Name', 'size' => '6')); ?>
				</div>
				<div class="row">	
					<?=$this->BlackprintForm->field('email', array('label' => 'E-mail', 'size' => '6', 'help' => 'Provide an e-mail address for the user to login with.')); ?>
				</div>
				<div class="row">	
					<?=$this->BlackprintForm->field('password', array('label' => 'Password', 'type' => 'password', 'size' => '6', 'placeholder' => 'Not your dog\'s name', 'help' => 'Choose a password at least 6 characters long.')); ?>
				</div>
				<div class="row">	
					<?=$this->BlackprintForm->field('passwordConfirm', array('label' => 'Confirm Password', 'type' => 'password', 'size' => '6', 'help' => 'Just to be sure, type the password again.')); ?>
				</div>

				<div class="row">	
					<?=$this->BlackprintForm->field('active', array('type' => 'checkbox', 'label' => 'Active', 'size' => '6', 'help' => 'If checked, this user will be active and able to login.')); ?>
				</div>

				
				<div class="row">
					<?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', array('library' => 'li3b_users', 'admin' => true, 'controller' => 'users', 'action' => 'index'), array('class' => 'btn')); ?>

					<p><br /><em><strong>Note:</strong> There will be no e-mail sent to this user. You must let them know what their password is.</em></p>
				</div>
			
			<?=$this->BlackprintForm->end(); ?>
	</div>
	
	<div class="col-md-3">
		<div class="well" style="padding: 8px 0;">
			<div style="padding: 8px;">
				<p>
					Blackprint provides three broad roles for users defined below. However, additional groups and permissions may be assigned to users in certain conditions for more detailed control. <?=$this->html->link('Read more about user access here.', array('library' => 'blackprint', 'controller' => 'documentation', 'action' => 'view', 'admin' => true, 'args' => array('users_and_permissions'))); ?>
				</p>
				<p>
					<strong>Administrator</strong><br />
					These users can create and update other users and have complete access to the administrative back-end.<br /><br />
					<strong>Content Editor</strong><br />
					In general, these users can create and update content using the administrative back-end, but can not create any new users.<br /><br />
					<strong>Registered User</strong><br />
					These users have, more or less, "read-only" access and can only view content published on the front-end of the site.
				</p>
			</div>
			
		</div>
	</div>
</div>