<?=$this->html->script('/blackprint/js/bootstrapUserValidation', array('inline' => false)); ?>
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<h3>Your User Settings</h3>
		</div>
		<?=$this->BlackprintForm->create($document, array('id' => 'user-update-form', 'class' => 'form-horizontal', 'role' => 'form', 'type' => 'file', 'onSubmit' => 'return submitCheck();')); ?>
			<?=$this->security->requestToken(); ?>
				<div class="row">
					<div>
						<?php
						if($document->profilePicture) {
							if(substr($document->profilePicture, 0, 4) == 'http') {
								echo $this->html->image($document->profilePicture, array('id' => 'profile-picture-preview', 'style' => 'width: 150px; height: 150px;'));
							} else {
								echo $this->html->image('/profilepic/' . $document->profilePicture, array('id' => 'profile-picture-preview', 'style' => 'width: 150px; height: 150px;'));
							}
						} else {
							echo $this->html->image('holder.js/150x150/text:None', array('id' => 'profile-picture-preview', 'style' => 'width: 150px; height: 150px; margin-bottom: 8px;'));
						}
						?>
					</div>
					<?=$this->BlackprintForm->field('profilePicture', array('label' => 'Upload New Profile Picture', 'type' => 'file', 'size' => '6', 'help' => 'Change by uploading a new image (no larger than 250x250px).')); ?>
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
					<?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', '/', array('class' => 'btn')); ?>
				</div>
			
			<?=$this->BlackprintForm->end(); ?>
	</div>

	<div class="col-md-3">
		<?php // $this->bootstrapBlock->render('blackprint_update_right', array('document' => $document)); // TODO: Rethink this... Lithium Bootstrap had blocks which could be hooked into. This one in particular allowed the social plugin to place some JavaScript that allowed the user profile picture to be set from social media sources. ?>
		<div class="well" style="padding: 8px 0;">
			<div style="padding: 8px;">
				<p>
					<strong>Notes</strong><br />
					If you change your password, you will not be e-mailed any sort of confirmation. Please do not forget your password.
					<br /><br />
					You may need to log out and back in again in order to see the changes you make.
				</p>
			</div>

		</div>
	</div>
</div>