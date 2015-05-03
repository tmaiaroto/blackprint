<?php
use \blackprint\models\Post;
use \blackprint\models\Config;
$defaults = Post::$defaultOptions;
$inherited = Config::get('default');
?>
<div class="row">
	<div class="col-md-12">
		<div class="row">
			<h2 id="page-heading">Blog Configuration</h2>
		</div>

		<?=$this->BlackprintForm->create($document, array('class' => 'form-horizontal', 'role' => 'form')); ?>
			<?=$this->security->requestToken(); ?>

			<div class="row pull-right">
				<?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'dashboard'), array('class' => 'btn')); ?> <?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?>
			</div>
			<div class="clearfix"></div>

			<ul class="nav nav-tabs">
				<!--<li><a href="#general" data-toggle="tab">General</a></li>-->
				<li><a href="#social" data-toggle="tab">Social</a></li>
			</ul>

			<div class="tab-content">
				<?php // GENERAL
				/* ?>
				<div class="tab-pane active" id="general">
					<div class="container">
						<br />
						<div class="row">
							<?=$this->BlackprintForm->field('siteName', array('type' => 'text', 'label' => 'Site Name', 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('adminEmail', array('type' => 'text', 'label' => 'Administrator\'s E-mail', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?>
							<?=$this->BlackprintForm->field('adminName', array('type' => 'text', 'label' => 'Administrator\'s Name', 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('privacyPolicyUrl', array('type' => 'text', 'label' => 'Privacy Policy URL', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?>
							<?=$this->BlackprintForm->field('termsOfServiceUrl', array('type' => 'text', 'label' => 'Terms of Service URL', 'size' => '6')); ?>
						</div>
					</div>
				</div>
				*/ ?>

				<?php // SOCIAL ?>
				<div class="tab-pane active" id="social">
					<div class="container">
						<div class="row">
							<h3>Social Sharing</h3>
							<p>By default, check which social sharing options are visible on blog posts. This can be overriden on a post by post basis.</p>
							<?php // email, facebook, twitter, googleplus, linkedin, pinterest, tumblr, reddit, github, youtube, pocket 

							foreach(array('email' => 'E-mail', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'googleplus' => 'Google+', 'linkedin' => 'LinkedIn', 'pinterest' => 'Pinterest', 'tumblr' => 'Tumblr', 'reddit' => 'Reddit', 'pocket' => 'Pocket') as $option => $label) {
								echo $this->BlackprintForm->field('sharing.email', array('type' => 'checkbox', 'checked' => (in_array($option, $defaults['socialSharing']) || (isset($document->sharing) && $document->sharing->{$option})), 'label' => false, 'help' => $label, 'size' => '2'));
							}
							?>
						</div>
						<div class="row">
							<h3>Open Graph Tags</h3>
							<p>Set the blog's default Open Graph tags. Blog post pages will inherit these values, but they can be overridden on a post by post basis when creating or updating blog posts. For more information about Open Graph tags, <?=$this->html->link('click here.', 'http://ogp.me/', array('target' => '_blank')); ?></p>
						</div>
						<div class="row">
							<?php
							$inheritedSiteName = isset($inherited['og']) && isset($inherited['og']['site_name']) && (!$document->og || !$document->og->site_name) ? $inherited['og']['site_name']:null;
							$inheritedSiteName = $document->og && $document->og->site_name ? $document->og->site_name:$inheritedSiteName;

							$inheritedTitle = isset($inherited['og']) && isset($inherited['og']['title']) && (!$document->og || !$document->og->title) ? $inherited['og']['title']:null;
							$inheritedTitle = $document->og && $document->og->title ? $document->og->title:$inheritedTitle;

							$inheritedDescription = isset($inherited['og']) && isset($inherited['og']['description']) && (!$document->og || !$document->og->description) ? $inherited['og']['description']:null;
							$inheritedDescription = $document->og && $document->og->description ? $document->og->description:$inheritedDescription;

							$inheritedImage = isset($inherited['og']) && isset($inherited['og']['image']) && (!$document->og || !$document->og->image) ? $inherited['og']['image']:null;
							$inheritedImage = $document->og && $document->og->image ? $document->og->image:$inheritedImage;
							?>
							<?=$this->BlackprintForm->field('og.site_name', array('type' => 'text', 'label' => 'Site Name', 'value' => $inheritedSiteName, 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?>
							<?=$this->BlackprintForm->field('og.title', array('type' => 'text', 'label' => 'Title', 'value' => $inheritedTitle, 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('og.description', array('type' => 'text', 'label' => 'Description', 'value' => $inheritedDescription, 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?>
							<?=$this->BlackprintForm->field('og.image', array('type' => 'text', 'label' => 'Image URL', 'value' => $inheritedImage, 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('og.type', array('type' => 'select', 'default' => 'article', 'options' => array(
								'website' => 'website',
								'article' => 'article',
								'book' => 'book',
								'profile' => 'profile',
								'video.movie' => 'video.movie',
								'video.episode' => 'video.episode',
								'video.tv_show' => 'video.tv_show',
								'video.other' => 'video.other',
								'music.song' => 'music.song',
								'music.album' => 'music.album',
								'music.playlist' => 'music.playlist',
								'music.radio_station' => 'music.radio_station'
							), 'label' => 'Object Type', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?>
						</div>
						
					</div>
				</div>

				
			</div> <?php // end tab content ?>
			
		<?=$this->BlackprintForm->end(); ?>
	</div>
	
</div>