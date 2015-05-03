<?=$this->html->script(array('/blackprint/js/jquery/colorpicker', '/blackprint/js/manageLabels.js', '/bower_components/bootstrap-toggle/js/bootstrap-toggle.min.js'), array('inline' => false)); ?>
<?=$this->html->style(array('/blackprint/css/jquery/colorpicker', '/blackprint/css/draft-sidebar', '/bower_components/bootstrap-toggle/css/bootstrap-toggle.min.css'), array('inline' => false)); ?>
<div id="draft-sidebar" class="col-xs-7 col-sm-4 col-md-3 sidebar sidebar-right sidebar-animate typeset" data-ignore=".colorpicker:parent">
	
	<h4>Content Settings</h3>
	
	<p>
		<input id="publish-status" type="checkbox" <?php echo ($document->published) ? 'checked':''; ?> data-toggle="toggle" data-style="pill" data-onstyle="success" data-offstyle="danger" data-on="published" data-off="unpublished">
	</p>
	

	<div class="row collapsible">
		<a class="collapsible-title" data-toggle="collapse" href="#collapseMeta" aria-expanded="false" aria-controls="collapseExample"><i class="dropdown icon"></i> <b>Metadata</b><i class="pull-right fa fa-angle-right"></i></a>
		<div class="collapse collapsible-body" id="collapseMeta">
			<small>Optional metadata to override the default values.</small>
			<?=$this->BlackprintForm->create($document, array('id' => 'update-meta', 'class' => 'form form-horizontal small', 'onSubmit' => 'updateMeta(); return false;')); ?>
				<?=$this->BlackprintForm->field('meta.description', array('label' => 'Description', 'size' => '12', 'class' => 'form-control input-sm', 'maxlength' => '40', 'placeholder' => 'meta description', 'autocomplete' => 'off')); ?>
				<?=$this->BlackprintForm->field('meta.keywords', array('label' => 'Keywords', 'size' => '12', 'class' => 'form-control input-sm', 'maxlength' => '40', 'placeholder' => 'meta keywords', 'autocomplete' => 'off')); ?>
				<?=$this->BlackprintForm->field('meta.author', array('label' => 'Author', 'size' => '12', 'class' => 'form-control input-sm', 'maxlength' => '40', 'placeholder' => 'meta author', 'autocomplete' => 'off')); ?>
			<?=$this->security->requestToken(); ?>
			<div class="clearfix"></div>
		</div>
		<?=$this->BlackprintForm->end(); ?>
	</div>

	<div class="row collapsible">
		<a class="collapsible-title" data-toggle="collapse" href="#collapseTags" aria-expanded="false" aria-controls="collapseExample"><i class="dropdown icon"></i> <b>Labels</b><i class="pull-right fa fa-angle-right"></i></a>
		<div class="collapse collapsible-body" id="collapseTags">
			<p>
				<?php // TODO: Pass these as options to a manageLabels() function of some sort. Have that also place all of the following HTML so everything is easily re-used elsewhere and nice and neat. Pass additional options for styling. ?>
				<input type="hidden" id="labels-document-id" value="<?=$document->_id; ?>" />
				<input type="hidden" id="labels-update-elements" value=".labels-wrapper" />
				<input type="hidden" id="labels-index-url" value="/blog" />
				<?php
				// TODO: This isn't necessary anymore...there is now an JSON route to handle applying and removing labels as they are clicked individually.
				// Previously this adjusted form input on the page so when the form was saved along with the rest of the model data, the associated labels would be saved as well.
				// This is still used by the JavaScript to apply the proper classes to show which labels are currently active, so keep it for now but remove it eventually.
				if($document->labels) {
					foreach($document->labels as $labelId) {
						echo '<input type="hidden" name="labels[]" value="' . $labelId . '" id="PostLabel' . $labelId . '" class="applied-post-label" />';
					}
				}
				?>
				<div id="current-labels-wrapper">
					<div id="current-labels"></div>
				</div>
				<div style="clear: left;"></div>
				<div id="labels-mode"><a href="#" id="manage-existing-labels">manage existing labels</a></div>

				<?=$this->BlackprintForm->create(null, array('id' => 'create-new-label', 'class' => 'form form-horizontal small', 'onSubmit' => 'saveLabel(); return false;')); ?>
					<div class="field new-label-input-field">
						<?=$this->BlackprintForm->field('name', array('label' => false, 'id' => 'new-label-name', 'class' => 'form-control input-sm', 'size' => '8', 'maxlength' => '40', 'placeholder' => 'New label name', 'autocomplete' => 'off')); ?>
						<?=$this->BlackprintForm->submit('Save Label', array('class' => 'btn btn-secondary btn-sm', 'style' => 'height: 30px', 'id' => 'create-new-label-button')); ?>
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
						</div>
					<div class="clearfix"></div>
				<?=$this->BlackprintForm->end(); ?>
				<?php // END.labels ?>
				</p>
		</div>

		
	</div>

</div>
<script type="text/javascript">
function updateMeta() {}

$(function(){
	
	$('#publish-status').change(function() {
		if($(this).prop('checked')) {
			publishUrl = '/admin/blackprint/posts/publish/<?=$document->_id; ?>/true.json';
		} else {
			publishUrl = '/admin/blackprint/posts/publish/<?=$document->_id; ?>/false.json';
		}
		$.ajax({
			type: 'POST',
			url: publishUrl,
			data: {},
			success: function(data) {
				if(data.success !== true) {
					//$('#publish-status').bootstrapToggle('off'); // loop!
				}
			}
		});
	});
	
	$('.collapse').on('show.bs.collapse', function () {
		$('i.pull-right', $(this).prev('.collapsible-title')).removeClass("fa-angle-right").addClass("fa-angle-down");
    });
    $('.collapse').on('hide.bs.collapse', function () {
    	$('i.pull-right', $(this).prev('.collapsible-title')).removeClass("fa-angle-down").addClass("fa-angle-right");
    });

	//$('#draft-sidebar').sidebar('setting', {"dimPage": false, "transition": "overlay"});
	//$('#draft-sidebar').sidebar('attach events', '.open-draft-panel');

	//$('#draft-sidebar').sidebar('show');

	//$('.ui.accordion').accordion();
});
</script>