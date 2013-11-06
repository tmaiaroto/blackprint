<div class="row">
	<div class="col-md-9">
		<h2 id="page-heading">Create New Page</h2>
		<br />
		<?=$this->BlackprintForm->create($document, array('class' => 'form-horizontal', 'role' => 'form')); ?>
			<?=$this->security->requestToken(); ?>
				<div class="row">
					<?=$this->BlackprintForm->field('title', array('label' => 'Title', 'size' => '9')); ?>
				</div>
				<div class="row">
					<?=$this->BlackprintForm->field('body', array('type' => 'textarea', 'label' => 'Body', 'size' => '12', 'style' => 'width: 100%; height: 400px;', 'class' => 'wysiwyg wysihtml5')); ?>
				</div>
				<?php 
				// test two editors on page
				/*
				<div class="row">
					<?=$this->BlackprintForm->field('bodyTwo', array('type' => 'textarea', 'label' => 'Body', 'size' => '12', 'style' => 'width: 100%; height: 400px;', 'class' => 'wysiwyg wysihtml5')); ?>
				</div>
				*/ 
				?>
				<div class="row">
					<?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'content', 'action' => 'index'), array('class' => 'btn')); ?>
				</div>

			</fieldset>
	</div>

	<div class="col-md-3">
		<div class="well">
			<div class="row">
				<?=$this->BlackprintForm->field('published', array('type' => 'checkbox', 'label' => 'Published', 'help' => 'If checked, this content will be visible to the public.')); ?>
			</div>
		</div>
	</div>

	<?=$this->BlackprintForm->end(); ?>
</div>