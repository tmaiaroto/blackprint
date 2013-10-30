<div class="row">
	<div class="col-md-9">
		<h2 id="page-heading">Upload New Assets</h2>
		<br />
		<?=$this->BlackprintForm->create(null, array('class' => 'form-horizontal', 'role' => 'form', 'type' => 'file')); ?>
			<?=$this->security->requestToken(); ?>
				<div class="row">
					<?=$this->BlackprintForm->field('Filedata', array('type' => 'file', 'label' => 'File', 'size' => '12')); ?>
				</div>

				<div class="row">
					<?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'assets', 'action' => 'index'), array('class' => 'btn')); ?>
				</div>

			</fieldset>
	</div>

	<div class="col-md-3">
		<div class="well">
			<div class="row">
				
			</div>
		</div>
	</div>

	<?=$this->BlackprintForm->end(); ?>
</div>