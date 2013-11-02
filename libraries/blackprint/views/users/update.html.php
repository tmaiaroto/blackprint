<?php
$user = $this->request()->user;
$usersExternalServices = isset($user['externalAuthServices']) ? $user['externalAuthServices']:array();
?>
<?=$this->html->script('/blackprint/js/manageUser', array('inline' => false)); ?>
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
				</p>
			</div>
		</div>

		<?php if(count($externalAuthServices) > 0) { ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				Third Party Services
			</div>
			<div class="panel-container">		
				<p class="mg-top-10">You can login using your account from the following services:</p>
				<?php foreach($externalAuthServices as $k => $v) { ?>
					<div class="row mg-bottom-10">
						<div class="col-md-3">
							<?php
							if(isset($usersExternalServices[$k]['profilePicture']) && !empty($usersExternalServices[$k]['profilePicture'])) {
								echo '<img src="' . $usersExternalServices[$k]['profilePicture'] . '" width="50" style="margin: 5px 0;" />';
							}
							?>
						</div>
						<div class="col-md-9">
							<?php echo $v['logo']; ?> <?=$v['name']; ?><br />
							<?=$this->html->link('Revoke', array('library' => 'blackprint', 'controller' => 'users', 'action' => 'revoke_service', 'args' => array($k)), array('class' => 'btn btn-small btn-default')); ?> 
							<?php 
							if(!isset($usersExternalServices[$k]) || empty($usersExternalServices[$k])) {
								echo $this->html->link('Link', array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login', 'args' => array($k)), array('class' => 'btn btn-small btn-primary'));
							} else {
								if(isset($usersExternalServices[$k]['profilePicture']) && !empty($usersExternalServices[$k]['profilePicture'])) {
									echo '<br /><a href="#" onClick="setProfilePictureFromUrl(\'' . $usersExternalServices[$k]['profilePicture'] . '\');" class="small">Use for Profile Picture</a>';
								}
							} ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>

	</div>
</div>