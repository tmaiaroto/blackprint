<?=$this->html->script('/blackprint/js/full-rainbow.min.js', array('inline' => false)); ?>
<style type="text/css">pre { overflow: auto; word-wrap: normal; white-space: pre; } code { overflow:auto; } </style>
<?=$this->html->style('/blackprint/css/rainbow-themes/blackboard.css', array('inline' => false)); ?>
<div class="row post-index-container">
	<div class="col-xs-12 col-sm-6 col-md-8 mg-top-20">
			<?php foreach($documents as $document) { ?>
				<div class="post-summary">
					<?php $active = ($document->active) ? 'active':'inactive'; ?>
					<?php echo $this->html->link($document->title, array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'read', 'args' => array($document->url)), array('escape' => false)); ?>
					<?php echo $this->Blackprint->containsSyntax($this->Blackprint->purify($this->Blackprint->truncateHtml($document->body, 350))); ?>
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
									}
								}

							}
							?>
						</small>
					</div>
				</div>
			<?php } ?>
		<?=$this->BlackprintPaginator->paginate(); ?>
	</div>

	<div class="col-xs-12 col-sm-6 col-md-4 post-index-sidebar">
		<h3>Search for Posts</h3>
		<?=$this->Blackprint->queryForm(); ?>
	</div>

</div>
<script type="text/javascript">
	$('.user-info').tip();
</script>