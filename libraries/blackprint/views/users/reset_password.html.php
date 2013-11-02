<?=$this->html->script('/blackprint/js/manageUser', array('inline' => false)); ?>
<div class="row">
	<div class="col-md-6">
	<?php if(!empty($document)) { ?>
		<?=$this->BlackprintForm->create($document, array('class' => 'form-horizontal', 'onSubmit' => 'return submitCheck();')); ?>
	<?php } else { ?>
		<?=$this->BlackprintForm->create(null, array('class' => 'form-horizontal')); ?>
	<?php } ?>
		<?=$this->security->requestToken(); ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				Reset Your Password
			</div>
			<div class="container">
				<?php if(!empty($document)) { ?>
					<div class="mg-top-10">
						<?=$this->BlackprintForm->field('email', array('type' => 'text', 'label' => 'E-mail Address', 'groupClass' => 'col-md-12')); ?>
					</div>
					<div class="row mg-top-10 mg-bottom-15">
						<?=$this->BlackprintForm->field('password', array('type' => 'password', 'placeholder' => 'Not your dog\'s name', 'label' => 'Password', 'help' => 'Must be at least 6 characters long.', 'groupClass' => 'col-md-6')); ?>
						<?=$this->BlackprintForm->field('passwordConfirm', array('type' => 'password', 'label' => 'Confirm Password', 'help' => 'Just to be sure, type the password again.', 'groupClass' => 'col-md-6')); ?>
					</div>
				<?php } else { ?>
				<div id="register-fields">
					<div class="row mg-top-10 mg-bottom-10">
						<?=$this->BlackprintForm->field('email', array('type' => 'text', 'label' => 'E-mail Address', 'groupClass' => 'col-md-12')); ?>
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
		<h2>Forgot Your Password? No Problem!</h2>
		<p>
			To recover your lost password, you'll actually need to reset it. Your password is never sent to you in plain text for security reasons, so please remember it. The only way we have to verify you now is e-mail, so we'll send along some instructions if you submit the form to the left.
		</p>
	</div>
	<?=$this->BlackprintForm->end(); ?>
</div>