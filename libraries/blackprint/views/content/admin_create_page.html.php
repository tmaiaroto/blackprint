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
					<?=$this->BlackprintForm->field('summary', array('type' => 'textarea', 'label' => 'Summary', 'size' => '12', 'help' => 'This is an optional field. If you don\'t provide specific summary copy, a portion from the body copy may be used. It depends on the site design.')); ?>
				</div>
				<div class="row">
					<?=$this->BlackprintForm->field('body', array('type' => 'textarea', 'label' => 'Body', 'size' => '12')); ?>
				</div>

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