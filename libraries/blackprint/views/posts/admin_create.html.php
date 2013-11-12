<?=$this->html->script(array('/blackprint/js/jquery/colorpicker', '/blackprint/js/manageBlog.js'), array('inline' => false)); ?>
<?=$this->html->style(array('/blackprint/css/jquery/colorpicker'), array('inline' => false)); ?>
<div class="row">
	<div class="col-md-9">
		<h2 id="page-heading">Create New Post</h2>
		<br />
		<?=$this->BlackprintForm->create($document, array('class' => 'form-horizontal')); ?>
			<?=$this->security->requestToken(); ?>
			<div class="row">
				<?=$this->BlackprintForm->field('title', array('label' => 'Title', 'size' => '9')); ?>
			</div>
			<div class="row">
				<?=$this->BlackprintForm->field('subtitle', array('label' => 'Subtitle', 'size' => '9')); ?>
			</div>
			<div class="row">
				<?php
					$authorAlias = isset($this->_request->user['firstName']) ? $this->_request->user['firstName']:'';
					$authorAlias = isset($this->_request->user['lastName']) ? $authorAlias . ' ' . $this->_request->user['lastName']:$authorAlias;
					$authorAlias = $document->authorAlias ? $document->authorAlias:$authorAlias;
					?>
				<?=$this->BlackprintForm->field('authorAlias', array('label' => 'Author', 'size' => '9', 'value' => $authorAlias)); ?>
			</div>
			<div class="row">
				<?=$this->BlackprintForm->field('body', array('label' => 'Body', 'type' => 'textarea', 'style' => 'width: 100%; height: 400px;', 'groupClass' => 'form-group col-md-12', 'class' => 'wysiwyg wysihtml5')); ?>
			</div>

			
			<div id="PostLabelsInputs">
				<?php
				if($document->labels) {
					foreach($document->labels as $labelId) {
						echo '<input type="hidden" name="labels[]" value="' . $labelId . '" id="PostLabel' . $labelId . '" class="applied-post-label" />';
					}
				}
				?>
			</div>

			<div class="row">
				<?=$this->BlackprintForm->submit('Save', array('class' => 'btn btn-primary')); ?> <?=$this->html->link('Cancel', array('library' => 'blackprint', 'admin' => true, 'controller' => 'posts', 'action' => 'index'), array('class' => 'btn')); ?>
			</div>
	</div>

	<div class="col-md-3">
		<div class="well" style="padding: 8px 0;">
			<div class="panel-container">
				<div class="row">	
					<?=$this->BlackprintForm->field('published', array('type' => 'checkbox', 'label' => 'Active', 'size' => '12', 'help' => 'If checked, this post will be visible on the front-end of the site.')); ?>
				</div>

				<?php /*
				<div class="row">
					<?=$this->BlackprintForm->field('options.highlightTheme', array('type' => 'select', 'size' => '12', 'label' => 'Code Highlighting Theme', 'options' => $highlightThemes, 'help' => '<a href="#" rel="tooltip" class="tip" data-original-title="If your content has any program code snippets, you can choose a color theme.">[What\'s this?]</a>')); ?>
				</div>
				*/ ?>

				<?=$this->BlackprintForm->end(); ?>
			</div>

			<div class="panel-container">
				<label>Tags</label>
				<div id="current-labels-wrapper">
					<div id="current-labels"></div>
				</div>
				<div style="clear: left;"></div>
				<div id="labels-mode"><a href="#" id="manage-existing-labels">manage existing labels</a></div>

				<?=$this->BlackprintForm->create(null, array('id' => 'create-new-label', 'onSubmit' => 'saveLabel(); return false;')); ?>
					<div class="row">
						<?=$this->BlackprintForm->field('name', array('label' => false, 'id' => 'new-label-name', 'size' => '12', 'maxlength' => '40', 'placeholder' => 'New label name', 'autocomplete' => 'off')); ?>
					</div>
						<div class="label-colors" style="display:none;">
							<div class="label-color-input">
								<div id="label-color" class="colorSelector"><div id="label-chosen-color" style="background-color: #ffffff;"></div></div>
								<input type="hidden" value="#ffffff" name="color" id="label-color-input" />
							</div>
							<div class="label-color-input">
								<div id="label-bg-color" class="colorSelector"><div id="label-chosen-bg-color" style="background-color: #0000ff;"></div></div>
								<input type="hidden" value="#0000ff" name="bgColor" id="label-bg-color-input" />
							</div>
							<span id="label-preview" class="label" style="background-color: #0000ff;">Label Preview</span>
							<br style="clear: left;" />
							<?=$this->BlackprintForm->submit('Save Label', array('class' => 'btn', 'id' => 'create-new-label-button')); ?>
						</div>
					<div class="clearfix"></div>
				<?=$this->BlackprintForm->end(); ?>
			</div>

		</div>
	</div>
</div>