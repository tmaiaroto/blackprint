<?=$this->html->script('/blackprint/js/manageUser', array('inline' => false)); ?>
<div class="row">
	<div class="col-md-6">
	<?=$this->BlackprintForm->create($document, array('class' => 'form-horizontal', 'id' => 'register-form', 'onSubmit' => 'return submitCheck();')); ?>
		<?=$this->security->requestToken(); ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php if(!empty($externalRegistration)) { ?>
					<span id="complete-registration-text">Complete Your Registration</span> <small class="pull-right"><a href="#" id="already-have-account-link" onClick="linkNewAccount();">I already have an account</a></small>
				<?php } else { ?>
					Register
				<?php } ?>
			</div>
			<div class="panel-container">
				<?php if(!empty($externalRegistration)) { ?>
					<div id="register-new-service" class="mg-top-10" style="display:none;">
						Login using your existing account on this site and link your account from <?=$externalRegistration['serviceName']; ?>.
					</div>
				<?php } ?>
				<div id="register-fields">
					<div class="row mg-top-10">
						<?=$this->BlackprintForm->field('firstName', array('type' => 'text', 'label' => 'First Name', 'groupClass' => 'col-md-6')); ?>
						<?=$this->BlackprintForm->field('lastName', array('type' => 'text', 'label' => 'Last Name', 'groupClass' => 'col-md-6')); ?>
					</div>
					<div class="row mg-top-10">
						<?=$this->BlackprintForm->field('email', array('type' => 'text', 'label' => 'E-mail Address', 'groupClass' => 'col-md-12')); ?>
					</div>
					<div class="row mg-top-10 mg-bottom-10">
						<?=$this->BlackprintForm->field('password', array('type' => 'password', 'placeholder' => 'Not your dog\'s name', 'label' => 'Password', 'help' => 'Must be at least 6 characters long.', 'groupClass' => 'col-md-6')); ?>
						<?=$this->BlackprintForm->field('passwordConfirm', array('type' => 'password', 'label' => 'Confirm Password', 'help' => 'Just to be sure, type the password again.', 'groupClass' => 'col-md-6')); ?>
					</div>
				</div>
				<?php if(!empty($externalRegistration)) { ?>
					<div id="register-new-service-fields" style="display:none;">
						<div class="row mg-top-10 mg-bottom-10">
							<?=$this->BlackprintForm->field('emailLogin', array('type' => 'text', 'label' => 'E-mail Address', 'groupClass' => 'col-md-6')); ?>
							<?=$this->BlackprintForm->field('passwordLogin', array('type' => 'password', 'label' => 'Password', 'groupClass' => 'col-md-6')); ?>
						</div>
						<div class="row mg-bottom-15" style="padding-left: 15px;">
							<small><a href="#" id="register-new-account-link" onClick="registerNewAccount();">I don't have an account on this site yet.</a></small>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="panel-footer">
				<?=$this->BlackprintForm->submit('Submit', array('class' => 'btn btn-primary pull-right')); ?> <?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'index'), array('class' => 'btn pull-right')); ?>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<?php if(!empty($externalRegistration)) { ?>
			<h2>Register Using <?=$externalRegistration['serviceName']; ?></h2>
			<p>
				You logged in via <?=$externalRegistration['serviceName']; ?>, but you still need to register locally. This way you can associate multiple 3rd party services in the future if you choose (for example if you wanted to login with Facebook today and Twitter tomorrow). Otherwise, you would have multiple user accounts on this site.
			</p>
		<?php } else { ?>
			<h2>Register a New Account</h2>
			<p>
				To register, simply fill out the form on the left. There's only some basic information that's required to login but, once registered, you will be able to manage this information and additional details regarding your account.
			</p>
		<?php } ?>
	</div>
	<?=$this->BlackprintForm->end(); ?>
</div>

<?php if(!empty($externalRegistration)) { ?>
<script type="text/javascript">
var linkNewAccount = function() {
	$('#register-fields').hide();
	$('#register-new-service').show();
	$('#register-new-service-fields').show();
	$('#register-form').attr('onSubmit', '');
	$('#already-have-account-link').hide();
	$('#register-new-account-link').show();
	$('#complete-registration-text').text('Link New Account From Another Service');
	$('#UserPassword').val('');
	$('#UserPasswordConfirm').val('');
	$('#UserEmail').val('');
	$('#UserFirstName').val('');
	$('#UserLastName').val('');
};
var registerNewAccount = function() {
	$('#register-fields').show();
	$('#register-new-service').hide();
	$('#register-new-service-fields').hide();
	$('#register-form').attr('onSubmit', 'return submitCheck();');
	$('#already-have-account-link').show();
	$('#register-new-account-link').hide();
	$('#complete-registration-text').text('Complete Your Registration');
	$('#UserPassword').val('');
	$('#UserPasswordConfirm').val('');
	$('#UserEmail').val('');
	$('#UserFirstName').val('');
	$('#UserLastName').val('');

	$('#UserEmailLogin').val('');
	$('#UserPasswordLogin').val('');
};

</script>
<?php } ?>