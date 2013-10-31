<div class="row">
	<div class="col-md-9">
		<div class="row">
			<h2 id="page-heading">CMS Configuration</h2>
		</div>

		<?=$this->BlackprintForm->create($document, array('class' => 'form-horizontal', 'role' => 'form')); ?>
			<?=$this->security->requestToken(); ?>

			<div class="row pull-right">
				<?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'dashboard'), array('class' => 'btn')); ?> <?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?>
			</div>
			<div class="clearfix"></div>

			<ul class="nav nav-tabs">
				<li><a href="#general" data-toggle="tab">General</a></li>
				<li><a href="#assets" data-toggle="tab">Assets</a></li>
				<li><a href="#thirdPartyAuth" data-toggle="tab">3rd Party Authentication</a></li>
			</ul>

			<div class="tab-content">
				<?php // GENERAL ?>
				<div class="tab-pane active" id="general">
					<div class="container">
						<br />
						<div class="row">
							<?=$this->BlackprintForm->field('siteName', array('type' => 'text', 'label' => 'Site Name', 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('adminEmail', array('type' => 'text', 'label' => 'Administrator\'s E-mail', 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('privacyPolicyUrl', array('type' => 'text', 'label' => 'Privacy Policy URL', 'size' => '6')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('termsOfServiceUrl', array('type' => 'text', 'label' => 'Terms of Service URL', 'size' => '6')); ?>
						</div>
					</div>
				</div>

				<?php // ASSETS ?>
				<div class="tab-pane" id="assets">
					<div class="container">
						<br />
						<div class="row mg-bottom-10">
							<?=$this->html->link('Clear Image Thumbnail Cache', '/admin/clear-thumbnail-cache?redirect=' . $this->blackprint->here(false), array('class' => 'btn btn-warning', 'onClick' => 'return confirm(\'Are you sure you want to clear the thumbnail cache?\')', 'escape' => false)); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('assets.allowedExtensions', array('type' => 'text', 'label' => 'Allowed File Extensions', 'size' => '12', 'help' => 'A comma separated list of extensions that limits what type of files can be uploaded to the site.')); ?>
						</div>
						<div class="row">
							<?=$this->BlackprintForm->field('assets.appendToAllowedExtensionsDefault', array('type' => 'checkbox', 'label' => 'Append to Default Allowed Extensions', 'size' => '8', 'help' => 'If checked, the extensions you provided above will be appended to the list of file extensions allowed by default.')); ?>
						</div>
						<div class="row">
							<h4>Default File Extensions Allowed to be Uploaded</h4>
							<p>
								A restrictive set of file types are allowed to be uploaded by default. This includes various media files, documents, compressed files, and a few application files that are not Linux binaries (you should really zip any executable file). While files are stored in the database, which helps protect the system from malicious files being executed, this restrictive set of files further increases security. The allowed file extensions by default are as follows:
							</p>
							<p>
								<strong>
								<?php 
								$i=1;
								$extCount=count($defaultAllowedExtensions);
								foreach($defaultAllowedExtensions as $ext) {
									echo $ext;
									if($i < $extCount) {
										echo ', ';
									}
									$i++;
								}
								?>
								</strong>
							</p>
						</div>
					</div>
				</div>

				<?php // THIRD PARTY AUTHENTICATON SERVICES (that work with native auth) ?>
				<div class="tab-pane" id="thirdPartyAuth">
					<div class="container">
						<br />
						<div class="row">
							<p>
								Your site can allow users to register and login via third party services that use OAuth for authentication. This means users can login using Facebook, Twitter, Google, etc. and your site can utilize the APIs from these services.
							</p>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-facebook"></i> <strong>Facebook</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.facebook.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.facebook.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.facebook.name', array('type' => 'hidden', 'value' => 'Facebook')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.facebook.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-facebook"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.facebook.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-foursquare"></i> <strong>Foursquare</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.foursquare.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.foursquare.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.foursquare.name', array('type' => 'hidden', 'value' => 'Foursquare')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.foursquare.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-foursquare"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.foursquare.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-github"></i> <strong>GitHub</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.github.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.github.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.github.name', array('type' => 'hidden', 'value' => 'GitHub')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.github.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-github"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.github.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-google-plus"></i> <strong>Google</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.google.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.google.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.google.name', array('type' => 'hidden', 'value' => 'Google')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.google.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-google-plus"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.google.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-instagram"></i> <strong>Instagram</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.instagram.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.instagram.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.instagram.name', array('type' => 'hidden', 'value' => 'Instagram')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.instagram.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-instagram"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.instagram.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-linkedin"></i> <strong>LinkedIn</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.linkedin.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.linkedin.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.linkedin.name', array('type' => 'hidden', 'value' => 'LinkedIn')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.linkedin.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-linkedin"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.linkedin.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-windows"></i> <strong>Microsoft</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.microsoft.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.microsoft.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.microsoft.name', array('type' => 'hidden', 'value' => 'Microsoft')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.microsoft.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-windows"></i>')); ?>
								</div>
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.microsoft.scope', array('type' => 'text', 'label' => 'Scope', 'size' => '6', 'help' => 'Optional, if you don\'t know what to put, leave it blank.')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-twitter"></i> <strong>Twitter</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.twitter.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.twitter.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.twitter.name', array('type' => 'hidden', 'value' => 'Twitter')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.twitter.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-twitter"></i>')); ?>
								</div>
							</div>
						</div>

						<div class="panel panel-default">
							<div class="panel-heading">
								<i class="fa fa-tumblr"></i> <strong>Tumblr</strong>
							</div>
							<div class="container" style="margin: 10px;">
								<div class="row">
									<?=$this->BlackprintForm->field('externalAuthServices.tumblr.key', array('type' => 'text', 'label' => 'API Key', 'size' => '6', 'groupStyle' => 'margin-right: 10px;')); ?> 
									<?=$this->BlackprintForm->field('externalAuthServices.tumblr.secret', array('type' => 'text', 'label' => 'API Secret', 'size' => '6')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.tumblr.name', array('type' => 'hidden', 'value' => 'Tumblr')); ?>
									<?=$this->BlackprintForm->field('externalAuthServices.tumblr.logo', array('type' => 'hidden', 'value' => '<i class="fa fa-tumblr"></i>')); ?>
								</div>
							</div>
						</div>

					</div>
				</div>

			</div> <?php // end tab content ?>
			
		<?=$this->BlackprintForm->end(); ?>
	</div>
	
	<div class="col-md-3">
	</div>
</div>