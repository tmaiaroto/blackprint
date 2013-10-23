<?=$this->html->script('/blackprint/js/bootstrapUserValidation', array('inline' => false)); ?>
<div class="row">
	<div class="span12">
		<h2 id="page-heading">Registration</h2>
		<?php if(!empty($externalRegistration)) { ?>
			<p>
				You logged in via <?=$externalRegistration['serviceName']; ?>, but you still need to register your account. All that's required is your name, e-mail address, and your desired password. While <?=$externalRegistration['serviceName']; ?> is authenticating you, it is possible that you will want to login using a different service in the future. This allows you to use and login with multiple social media accounts with your single user here. Of course you will also be able to login using your e-mail address and chosen password as well.
			</p>
			<p>
				<strong>Note:</strong> If you have already logged in using a different service (Facebook, Google, Instagram, etc.) and registered, you should use the same e-mail address and password below that you used before. This will link the new third party service with your account here. In that case, you're not registering so much as you are adding a new login option. Alternatively, you can also link your other accounts from third party services from your account settings page if you were logged in.
			</p>
		<?php } ?>
		<br />
		<?=$this->form->create($document, array('class' => 'form-horizontal', 'onSubmit' => 'return submitCheck();')); ?>
			<?=$this->security->requestToken(); ?>
				<div class="control-group">
					<?=$this->form->label('UserFirstName', 'First Name', array('class' => 'control-label')); ?>
					<div class="controls">
						<?=$this->form->field('firstName', array('label' => false, 'placeholder' => 'John', 'class' => 'input-xlarge'));?>
					</div>
				</div>
				<div class="control-group">
					<?=$this->form->label('UserLastName', 'Last Name', array('class' => 'control-label')); ?>
					<div class="controls">
						<?=$this->form->field('lastName', array('label' => false, 'placeholder' => 'Doe', 'class' => 'input-xlarge'));?>
					</div>
				</div>
				<div class="control-group">
					<?=$this->form->label('UserEmail', 'E-mail', array('class' => 'control-label')); ?>
					<div class="controls">
						<?=$this->form->field('email', array('label' => false, 'class' => 'input-xlarge'));?>
						<p class="help-block">Provide an e-mail address to login with.</p>
					</div>
				</div>
				<div class="control-group">
					<?=$this->form->label('UserPassword', 'Password', array('class' => 'control-label')); ?>
					<div class="controls">
						<?=$this->form->field('password', array('type' => 'password', 'label' => false, 'placeholder' => 'Not your dog\'s name', 'class' => 'input-xlarge'));?>
						<p class="help-block">Choose a password at least 6 characters long.</p>
					</div>
				</div>
				<div class="control-group">
					<?=$this->form->label('UserPasswordConfirm', 'Confirm Password', array('class' => 'control-label')); ?>
					<div class="controls">
						<?=$this->form->field('passwordConfirm', array('type' => 'password', 'label' => false, 'class' => 'input-xlarge'));?>
						<p class="help-block">Just to be sure, type the password again.</p>
					</div>
				</div>
				
				<div class="form-actions">
					<?=$this->form->submit('Submit', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'index'), array('class' => 'btn')); ?>
				</div>
			<?=$this->form->end(); ?>
	</div>
</div>