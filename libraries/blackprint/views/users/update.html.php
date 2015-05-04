<?php
$user = $this->request()->user;
$usersExternalServices = isset($user['externalAuthServices']) ? $user['externalAuthServices']:array();
?>
<?=$this->html->script(array('/blackprint/js/manageUser', '/blackprint/js/md5.js'), array('inline' => false)); ?>
<div class="row update-profile-container">
	<div class="col-md-8">
		<div class="row mg-top-15 mg-bottom-20">
			<h3>Your User Settings</h3>
		</div>
		<?=$this->BlackprintForm->create($document, array('id' => 'user-update-form', 'class' => 'form-horizontal', 'role' => 'form', 'type' => 'file', 'onSubmit' => 'return submitCheck();')); ?>
			<?=$this->security->requestToken(); ?>
				<div class="row">
					<div class="col-md-3">
						<?php
						if($document->profilePicture) {
							if(substr($document->profilePicture, 0, 4) == 'http') {
								echo $this->html->image($document->profilePicture, array('id' => 'profile-picture-preview', 'style' => 'width: 150px; height: 150px;'));
							} else {
								echo $this->html->image('/profilepic/' . $document->profilePicture, array('id' => 'profile-picture-preview', 'style' => 'width: 150px; height: 150px;'));
							}
						} else {
							//echo $this->html->image('holder.js/150x150/text:None', array('id' => 'profile-picture-preview', 'style' => 'width: 150px; height: 150px; margin-bottom: 8px;'));
							echo '<div id="missing-avatar" class="missing-avatar-default"><i class="fa fa-user" style="font-size: 150px; color: #888; line-height: 150px;"></i></div>';
						}
						?>
					</div>
					<div class="col-md-8">
						<?=$this->BlackprintForm->field('profilePicture', array('label' => 'Upload New Profile Picture', 'type' => 'file', 'size' => '7', 'help' => 'Change by uploading a new image (no larger than 250x250px).')); ?>
						<br style="clear:left;" /><a href="#" id="use-gravatar"><div id="gravatar-wrapper" class="profile-avatar-service-choice" style="display:none;"><div id="gravatar-preview" style="width: 25px; height: 25px; display:block; float: left; background: #ccc;"></div> Use Gravatar</div></a>
						
					</div>
				</div>
	
				<div class="row">	
					<?=$this->BlackprintForm->field('email', array('label' => 'E-mail / Login', 'size' => '5', 'help' => 'Provide an e-mail address for the user to login with.')); ?>
				</div>
				<div class="row">	
					<?=$this->BlackprintForm->field('firstName', array('label' => 'First Name', 'size' => '5', 'groupStyle' => 'margin-right: 10px')); ?>
					<?=$this->BlackprintForm->field('lastName', array('label' => 'Last Name', 'size' => '5')); ?>
				</div>
				
				<div class="row">	
					<?=$this->BlackprintForm->field('password', array('label' => 'Password', 'type' => 'password', 'size' => '5', 'placeholder' => 'Not your dog\'s name', 'help' => 'Choose a password at least 6 characters long.', 'groupStyle' => 'margin-right: 10px')); ?>
					<?=$this->BlackprintForm->field('passwordConfirm', array('label' => 'Confirm Password', 'type' => 'password', 'size' => '5', 'help' => 'Just to be sure, type the password again.')); ?>
				</div>
				<div class="row">
					<?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', '/', array('class' => 'btn')); ?>
				</div>
			
			<?=$this->BlackprintForm->end(); ?>
	</div>

	<div class="col-md-4 update-profile-sidebar pg-top-15">
		<?php // $this->bootstrapBlock->render('blackprint_update_right', array('document' => $document)); // TODO: Rethink this... Lithium Bootstrap had blocks which could be hooked into. This one in particular allowed the social plugin to place some JavaScript that allowed the user profile picture to be set from social media sources. ?>
		<h3>Note</h3>
		<p>
			If you change your password, you will not be e-mailed any sort of confirmation. Please do not forget your password.
		</p>

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
<script type="text/javascript">
// get the email
var email = $('#UserEmail').val();

if(/^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email)) {
	var gravatarImgUrl = 'http://www.gravatar.com/avatar/' + md5(email);
	var gravatar = $('<img>').attr({src: gravatarImgUrl, width: '25px;'}).load(function() {
		$('#gravatar-preview').css({"background":"none"});
		$('#gravatar-wrapper').fadeIn();
		
		$('#gravatar-preview').append(gravatar);
		$('#use-gravatar').click(function(){
			$.ajax({
				type: "POST",
				url: "/set-profile-picture-from-url.json",
				data: {"url": gravatarImgUrl},
				success: function(){ $("#missing-avatar").html($('<img>').attr({src: gravatarImgUrl, width: '150px;'})); $("#missing-avatar").removeClass('missing-avatar-default'); },
				dataType: "json"
			});
		});
	});
	
}
</script>