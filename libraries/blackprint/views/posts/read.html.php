<?php
// No longer using Rainbow by default, but it has been left in in case you want to use it.
// echo $this->html->script('/blackprint/js/full-rainbow.min.js', array('inline' => false));
/*
if(isset($options['codeLineNumbers']) && (bool)$options['codeLineNumbers']) {
	echo $this->html->script('/blackprint/js/rainbow.linenumbers.min.js', array('inline' => false));
	echo '<style type="text/css">pre, code { word-wrap:normal; } </style>';
}
*/
//var_dump($options);
// To switch back to Rainbow, simply include: $options['rainbowTheme'] below instead.

// NOTE: Remember to include the medium_editor element in any template override, otherwise it will prevent editing.
?>
<?php 
// If this is the draft page, include the editor. TODO: Maybe make a helper.
if($this->request()->action == 'draft') { ?>
<?=$this->_render('element', 'medium_editor_scripts'); ?>
<?=$this->_render('element', 'medium_editor', array('editorId' => 'post-body', 'user' => $this->request()->user, 'document' => $document), array('library' => 'blackprint')); ?>
<?=$this->_render('element', 'medium_editor', array('editorId' => 'page-heading', 'config' => array('firstHeader' => 'h1', 'secondHeader' => 'h2', 'thirdHeader' => 'h3', 'buttons' => array('header1', 'header2', 'header3', 'justifyLeft', 'justifyCenter')), 'user' => $this->request()->user, 'document' => $document), array('library' => 'blackprint')); ?>
<?=$this->_render('element', 'draft_sidebar', array('document' => $document)); ?>
<!--<div style="display:block; height: 1.68rem; width: 100%;"></div>--><!-- add some space back to fit the editor align back to baseline grid -->
<?php } ?>

<?=$this->html->script('/blackprint/js/highlight.pack.js', array('inline' => false)); ?>
<?=$this->html->style(array('/blackprint/css/highlight-themes/'.$options['highlightTheme']), array('inline' => false)); ?>

<div class="row post-container">
    <div class="col-md-12">
		<div id="page-heading" data-field="title"><?php echo $document->title; ?></div>
		<div id="post-body" data-field="body">
			<?php
			echo $document->body;
			?>
		</div>

		<hr class="mg-bottom-5" />
		
		<div class="row mg-bottom-15">
			<div class="col-xs-12 labels-wrapper">
				<?php
				if($labels) {
					echo '<small>Labels</small> ';
					foreach($labels as $label) {
						echo $this->html->link('<span class="label" data-label-id="' . $label['_id'] . '" style="color: ' . $label['color'] . '; background-color: ' . $label['bgColor'] . '">' . $label['name'] . '</span>', array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'args' => array(urlencode($label['name']))), array('style' => 'text-decoration: none;', 'escape' => false));
					}
				}
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="post-social-sharing">
					<h4>Share This Story</h4>
					<?=$this->_render('element', 'social_sharing', array('pxWidth' => '200', 'message' => trim(strip_tags($document->title)))); ?>
				</div>
			
				<div class="post-meta">
					<div class="post-meta-avatar">
						<?php
						if($document->_author->profilePicture) {
							echo $this->html->image($document->_author->profilePicture, array('alt' => '', 'class' => 'img-circle avatar'));
						}
						?>
					</div>
					<div class="post-meta-author">
						<small>
							Posted <?=$this->Blackprint->dateAgo($document->created); ?> by <?php echo $document->_author->firstName . ' ' . $document->_author->lastName; ?><br />
							<?php
							if($document->_author->externalAuthServices) {
								foreach($document->_author->externalAuthServices as $service) {
									//var_dump($service);
									// Only display links out to profiles on certain services
									switch($service->service) {
										case 'twitter':
											echo $this->html->link('<i class="fa fa-twitter"></i>', 'https://www.twitter.com/' . $service->userName, array('escape' => false, 'class' => 'social-profile', 'target' => '_blank'));
											break;
										case 'facebook':
											echo $this->html->link('<i class="fa fa-twitter"></i>', 'https://www.facebook.com/' . $service->userName, array('escape' => false, 'class' => 'social-profile', 'target' => '_blank'));
											break;
									}
								}

							}
							?>
						</small>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
<script type="text/javascript">
$(function(){

	
	// Track "blog post" reads.
	blackprintTrack.read({
		"selector": "#post-body",
		"category": "blog post"
	});

	// Track all outbound links.
	$('a').click(function(e) {
		// Except the social sharing links. Those get tracked as a different event.
		if($(this).hasClass("rrssb-link")) {
			var type = "";
			var tmp = document.createElement ('a');
			tmp.href = $(this).attr('href');
			// For various social networks.
			switch(tmp.hostname) {
				case 'twitter.com':
				case 'www.twitter.com':
					type = "twitter";
					break;
				case 'facebook.com':
				case 'www.facebook.com':
					type = "facebook";
					break;
				case 'plus.google.com':
					type = "googlePlus"
					break;
				case 'getpocket.com':
					type = 'pocket';
					break;
				case 'pinterest.com':
				case 'www.pinterest.com':
					type = 'pinterest';
					break;
				case 'linkedin.com':
				case 'www.linkedin.com':
					type = 'linkedin';
					break;
				case 'reddit.com':
				case 'www.reddit.com':
					type = 'reddit';
					break;
				case 'tumblr.com':
				case 'www.tumblr.com':
					type = 'tumblr';
					break;
			}
			// For e-mail shares.
			if(tmp.protocol == 'mailto:') {
				type = 'email';
			}

			blackprintTrack.share({
				"type": type
			});
			return true;
		}

		// If the link is not opening in another window or frame, etc. then stop propagation. 
		// Otherwise, there might not be enough time to record the event.
		if($(this).attr('target') != '_blank') {
			e.preventDefault();
			e.stopPropagation();
		}

		if($(this).attr('href').substr(0, 4).toLowerCase() == 'http') {
			url = blackprintTrack.linkOut({
				"url": $(this).attr('href'),
				"trackDomainOnly": true,
				// Return the URL, stopping the redirect in the linkOut() function if opening in a new window.
				// Otherwise if the link is opening in a new window, there's no reason to redirect and the event will 
				// be recorded because there's plenty of time on the page still.
				"returnUrl": ($(this).attr('target') == '_blank')
			});
		}
	});

});
</script>