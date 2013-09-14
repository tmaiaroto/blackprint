<?php
use lithium\core\Libraries;
$config = Libraries::get('blackprint');
$navbarTitle = isset($config['navbarTitle']) ? $config['navbarTitle']:'Blackprint';
$navbarTitle = isset($config['adminNavbarTitle']) ? $config['adminNavbarTitle']:$navbarTitle;
?>
<div class="navbar navbar-fixed-top navbar-inverse admin-navbar">
	<div class="container">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		<div class="nav-collapse">
			<?=$this->html->link($this->html->image('/img/blackprint-logo-square-white-64.png', array('style' => 'width: 32px; margin-top: 8px;')), '/admin', array('class' => 'brand pull-left', 'escape' => false, 'style' => 'margin-right: 8px;')); ?>

			<?=$this->blackprintMenu->render('admin', array('menuClass' => 'nav navbar-nav', 'activeClass' => 'active')); ?>

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
							<li><?=$this->html->link('Settings', '/settings'); ?></li>
							<li class="divider"></li>
							<li><a href="/logout">Sign Out</a></li>
						</ul>
					</li>
				</ul>
			<?php } ?>
			
		</div><!--/.nav-collapse -->
	</div><!--/.container -->
</div><!--/.navbar -->