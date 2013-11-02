<div class="row">
	<div class="col-md-6">
	<?=$this->BlackprintForm->create(null, array('class' => 'form-horizontal')); ?>
		<div class="panel panel-default">
			<div class="container panel-container">
				<div class="row mg-top-10">
					<?=$this->BlackprintForm->field('email', array('label' => 'E-mail Address', 'size' => '12')); ?>
				</div>
				<div class="row">
					<?=$this->BlackprintForm->field('password', array('label' => 'Password', 'type' => 'password', 'size' => '12')); ?>
				</div>
				
			</div>
			<div class="panel-footer">
				<?=$this->BlackprintForm->submit('Log in', array('class' => 'btn btn-primary pull-right')); ?>
				<?=$this->html->link('Register', array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register'), array('class' => 'btn')); ?>
				<div class="clearfix"></div>
			</div>
		</div>
		<?php if($canResetPassword) { ?>
			<?=$this->html->link('<small>Forget your password? Click here to reset it.</small>', array(), array('escape' => false)); ?>
		<?php } ?>
	</div>

	<div class="col-md-6">
		<h2>Login With Your Account</h2>
		<p>
			Please enter your e-mail address and password to login. If you don't have an account yet, you can <?=$this->html->link('click here to register.', array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register')); ?>
		</p>
		<?php 
		if(!empty($externalAuthServices)) {
			if(count($externalAuthServices) > 1) {
				echo '<p>You can also login using any of the following services.</p>';
				foreach($externalAuthServices as $k => $service) {
					echo '<span style="width: 18px; display: block; float: left;">' . $service['logo'] . '</span> ' . $this->html->link($service['name'], '/login/' . $k, array('class' => '', 'escape' => false)) . '<br />';
				}
			} else {
				foreach($externalAuthServices as $k => $service) {
					echo '<p>You can also login using <span style="width: 18px; display: block; float: left;">' . $service['logo'] . '</span> ' . $this->html->link($service['name'], '/login/' . $k, array('class' => '', 'escape' => false)) . '</p>';
				}
			}
		}
		?>
	</div>
	<?=$this->BlackprintForm->end(); ?>
</div>