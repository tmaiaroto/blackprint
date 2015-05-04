<!-- margin-bottom: 0.58rem; provides enough space to get things aligned to the baseline again, after changing menu font size, this changed to: 0.1rem -->
<nav class="navbar navbar-fixed-top navbar-default site-navbar">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#blackprint-site-navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">
			<?=$this->html->link($this->html->image('/img/blackprint-logo-square-64.png', array('style' => 'width: 32px; margin-top: 8px;')), '/', array('class' => 'brand pull-left', 'escape' => false, 'style' => 'margin-right: 8px;')); ?>
			</a>
		</div>
		
		<div class="collapse navbar-collapse" id="blackprint-site-navbar-collapse">
			<?=$this->blackprintMenu->render('public', array('menuClass' => 'nav navbar-nav', 'activeClass' => 'active')); ?>

			<?php if($user = $this->request()->user) { ?>
				<ul class="nav navbar-nav user_menu pull-right">
					<?php if($user['role'] == 'administrator' || $user['role'] == 'editor') { ?>
						<?php if($this->request()->action == 'draft') { ?>
						<li>
							<button class="btn btn-default btn-no-outline navbar-btn" data-toggle="sidebar" data-target="#draft-sidebar" href="#"><small>Content</small> <i class="fa fa-gear"></i></button>
						</li>
						<?php } ?>
						<?php // TODO: this is a little silly. make simpler.
						if($this->request()->action !== 'draft' && isset($document) && !empty($document) && is_object($document->_author) && $document->_author->_id == $user['_id']) { ?>
						<li>
							<button class="btn btn-default btn-no-outline navbar-btn" onClick="window.location.href='/blog/draft/<?=$document->draftHash; ?>'"><small>Edit</small> <i class="fa fa-pencil"></i></button>
						</li>
						<?php } ?>
					<?php } ?>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" role="button" href="#" aria-expanded="false">
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
					
						<ul class="dropdown-menu" role="menu">
							<li><?=$this->html->link('My Account', '/my-account'); ?></li>
							<li class="divider"></li>
							<li><a href="/logout">Sign Out</a></li>
						</ul>
					</li>
				</ul>
			<?php } ?>
			
		</div><!--/.nav-collapse -->
		<div class="container nav-rule"></div><!-- centered container -->
	</div><!--/.container -->
</nav><!--/.navbar -->
<script type="text/javascript">
$(function() {
	$('.site-navbar.dropdown').dropdown();
});
</script>
