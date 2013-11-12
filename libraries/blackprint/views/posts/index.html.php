<?=$this->html->script('/blackprint/js/full-rainbow.min.js', array('inline' => false)); ?>
<style type="text/css">pre { overflow: auto; word-wrap: normal; white-space: pre; } code { overflow:auto; } </style>
<?=$this->html->style('/blackprint/css/rainbow-themes/blackboard.css', array('inline' => false)); ?>
<div class="row">
	<div class="span9">
			<?php foreach($documents as $document) { ?>
					<?php $active = ($document->active) ? 'active':'inactive'; ?>
					<h1><?=$this->html->link($document->title, array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'view', 'args' => array($document->url))); ?></h1>
					<p><em>Posted <?=$this->time->to('words', $document->created); ?><?php echo $document->authorAlias ? ' by ' . $document->authorAlias:''; ?>.</em></p>
					<?php echo $this->html->containsSyntax($document->body); ?>
					<hr /><br />
			<?php } ?>
		<?=$this->BlackprintPaginator->paginate(); ?>
	</div>

	<div class="span3">
		<div class="well" style="padding: 8px 0;">
			<div style="padding: 8px;">
				<h3>Search for Posts</h3>
				<?=$this->html->queryForm(); ?>
			</div>
		</div>
	</div>

</div>
<script type="text/javascript">
	$('.user-info').tip();
</script>