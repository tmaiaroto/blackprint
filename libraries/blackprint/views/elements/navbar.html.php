<div class="navbar navbar-fixed-top navbar-default site-navbar">
	<div class="container">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		<div class="nav-collapse">
			<?=$this->html->link($this->html->image('/img/blackprint-logo-square-64.png', array('style' => 'width: 32px; margin-top: 8px;')), '/', array('class' => 'brand pull-left', 'escape' => false, 'style' => 'margin-right: 8px;')); ?>

			<?=$this->blackprintMenu->render('public', array('menuClass' => 'nav navbar-nav', 'activeClass' => 'active')); ?>

			<?php if($user = $this->request()->user) { ?>
				<ul class="nav navbar-nav user_menu pull-right">
					<li class="dropdown">
						<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
							<?php
							// Some common fields...Adjust this for your application's needs.
							// This also assumes you set the user object in the request.
							$username = isset($user['firstName']) ? $user['firstName']:'';
							$username = isset($user['lastName']) ? $username . ' ' . $user['lastName']:'';
							$username = isset($user['username']) ? $user['username']:$username;
							?>
							<i class="icon-user"></i> <?=$username; ?>
							<span class="caret"></span>
						</a>
					
						<ul class="dropdown-menu">
							<li><?=$this->html->link('My Account', '/my-account'); ?></li>
							<li class="divider"></li>
							<li><a href="/logout">Sign Out</a></li>
						</ul>
					</li>
				</ul>
			<?php } ?>
			
		</div><!--/.nav-collapse -->
	</div><!--/.container -->
</div><!--/.navbar -->