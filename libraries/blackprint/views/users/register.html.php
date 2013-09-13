<?=$this->html->script('/blackprint/js/bootstrapUserValidation', array('inline' => false)); ?>
<div class="row">
	<div class="span12">
		<h2 id="page-heading">Registration</h2>
		<br />
		<?=$this->form->create($document, array('class' => 'form-horizontal', 'onSubmit' => 'return submitCheck();')); ?>
			<fieldset>
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
						<p class="help-block">Provide an e-mail address for the user to login with.</p>
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
			</fieldset>
			<?=$this->form->end(); ?>
	</div>
</div>