<div class="row">
	<div class="col-md-9">
		<h2 id="page-heading">Log in With Your Account</h2>
		<br />
		<div class="clear"></div>
		<?=$this->BlackprintForm->create(null, array('class' => 'form-horizontal')); ?>
			<div class="row">	
				<?=$this->BlackprintForm->field('email', array('label' => 'E-mail', 'size' => '6')); ?>
			</div>
			<div class="row">	
				<?=$this->BlackprintForm->field('password', array('label' => 'Password', 'type' => 'password', 'size' => '6')); ?>
			</div>

			<div class="row">
				<?=$this->BlackprintForm->submit('Log in', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Register', array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register'), array('class' => 'btn')); ?>
			</div>
		<?=$this->BlackprintForm->end(); ?>
	</div>
</div>