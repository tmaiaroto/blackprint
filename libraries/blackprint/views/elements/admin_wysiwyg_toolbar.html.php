<?php $this->blackprintAsset->style(array('/blackprint/css/wysihtml-toolbar.css', '/bower_components/spectrum/spectrum.css', '/blackprint/css/spectrum-themes/dark.css'), array('inline' => false)); ?>
<?php $this->blackprintAsset->script(array('/bower_components/spectrum/spectrum.js'), array('inline' => false)); ?>
<?php /* <ul id="wysihtml-toolbar" class="wysihtml5-toolbar" style="display: none;">
  <li><a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-command="bold">bold</a></li>
  <li><a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-command="italic">italic</a></li>
  
  <!-- Some wysihtml5 commands require extra parameters -->
  <li><a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-command="foreColor" data-wysihtml5-command-value="red">red</a></li>
  <li><a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-command="foreColor" data-wysihtml5-command-value="green">green</a></li>
  <li><a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-command="foreColor" data-wysihtml5-command-value="blue">blue</a></li>
  
  <!-- Some wysihtml5 commands like 'createLink' require extra paramaters specified by the user (eg. href) -->
  <li><a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-command="createLink">insert link</a>
  <div data-wysihtml5-dialog="createLink" style="display: none;">
	<label>
	  Link:
	  <input data-wysihtml5-dialog-field="href" value="http://" class="text">
	</label>
	<a class="btn btn-default btn" tabindex="-1" unselectable="on" data-wysihtml5-dialog-action="save">OK</a> <a data-wysihtml5-dialog-action="cancel">Cancel</a>
  </div></li>
</ul> */ ?>

<ul id="wysihtml-toolbar" class="wysihtml5-toolbar">
   <li class="dropdown">
      <a class="btn btn-default btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-font"></i>&nbsp;<span class="current-font">Normal text</span>&nbsp;<b class="caret"></b></a>
      <ul class="dropdown-menu">
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="div" tabindex="-1" href="javascript:;" unselectable="on">Normal text</a></li>
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1" tabindex="-1" href="javascript:;" unselectable="on">Heading 1</a></li>
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" tabindex="-1" href="javascript:;" unselectable="on">Heading 2</a></li>
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h3" tabindex="-1" href="javascript:;" unselectable="on">Heading 3</a></li>
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h4" href="javascript:;" unselectable="on">Heading 4</a></li>
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h5" href="javascript:;" unselectable="on">Heading 5</a></li>
         <li><a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h6" href="javascript:;" unselectable="on">Heading 6</a></li>
      </ul>
   </li>
   <li class="dropdown">
   		<div class="block">
		  <a id="wysihtml-forecolor" class="btn btn-default" data-wysihtml5-command="foreColorStyle"><i class="fa fa-paint-brush"></i></a>
		  <div data-wysihtml5-dialog="foreColorStyle" style="display: none;">
		    <input id="wysihtml-forecolor-value" type="text" data-wysihtml5-dialog-field="color" style="display:none;" value="rgba(0,0,0,1)" />
		  </div>
		</div>
   </li>
   <li>
      <div class="btn-group"><a class="btn btn-default btn" data-wysihtml5-command="bold" title="CTRL+B" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-bold"></i></a><a class="btn btn-default btn" data-wysihtml5-command="italic" title="CTRL+I" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-italic"></i></a><a class="btn btn-default btn" data-wysihtml5-command="underline" title="CTRL+U" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-underline"></i></a></div>
   </li>
   <li>
      <div class="btn-group"><a class="btn btn-default btn" data-wysihtml5-command="insertUnorderedList" title="Unordered list" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-list-ul"></i></a><a class="btn btn-default btn" data-wysihtml5-command="insertOrderedList" title="Ordered list" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-list-ol"></i></a><a class="btn btn-default btn" data-wysihtml5-command="Outdent" title="Outdent" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-outdent"></i></a><a class="btn btn-default btn" data-wysihtml5-command="Indent" title="Indent" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-indent"></i></a></div>
   </li>
   <li>
      <div class="btn-group"><a class="btn btn-default btn" data-wysihtml5-action="change_view" title="Edit HTML" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-code"></i></a></div>
   </li>
   <li>

      <div class="bootstrap-wysihtml5-insert-link-modal modal fade">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <a class="close" data-dismiss="modal">×</a>
                  <h3 class="modal-title">Insert link</h3>
               </div>
               <div class="modal-body"><input value="http://" class="bootstrap-wysihtml5-insert-link-url form-control"><label class="checkbox"> <input type="checkbox" class="bootstrap-wysihtml5-insert-link-target" checked="">Open link in new window</label></div>
               <div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a><a href="#" class="btn btn-primary" data-dismiss="modal">Insert link</a></div>
            </div>
         </div>
      </div>
      <a class="btn btn-default" data-wysihtml5-command="createLink" title="Insert link" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-link"></i></a>
   </li>
   <li>
		<!-- Toolbar link -->
		<a class="btn btn-default btn" data-wysihtml5-command="insertImage" title="Insert image" tabindex="-1" data-toggle="modal" data-target="#insert-image-modal" href="javascript:;" unselectable="on"><i class="fa fa-picture-o"></i></a>
		<!-- Dialog -->
		<div data-wysihtml5-dialog="insertImage" id="insert-image-modal" style="display: none;" class="modal fade">
			<div class="modal-dialog">
          		<div class="modal-content">
	          		<div class="modal-header">
	                 	<a class="close" data-dismiss="modal">×</a>
                		<h3 class="modal-title">Insert Image</h3>
	               	</div>
					<div class="modal-body">
						<label>
						URL: <input data-wysihtml5-dialog-field="src" value="http://">
						</label>
						<label>
						Alternative text: <input data-wysihtml5-dialog-field="alt" value="">
						</label>
					</div>
					<div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a><a href="#" class="btn btn-primary" data-dismiss="modal">Insert Image</a></div>
				</div>
			</div>
		</div>


      <?php /* <div class="bootstrap-wysihtml5-insert-image-modal modal fade">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <a class="close" data-dismiss="modal">×</a>
                  <h3 class="modal-title">Insert image</h3>
               </div>
               <div class="modal-body"><input value="http://" class="bootstrap-wysihtml5-insert-image-url form-control"></div>
               <div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a><a href="#" class="btn btn-primary" data-dismiss="modal">Insert image</a></div>
            </div>
         </div>
      </div>
      <a class="btn btn-default btn" data-wysihtml5-command="insertImage" title="Insert image" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-picture-o"></i></a>
      */?>
   </li>
   <li>
      <div class="btn-group"><a class="btn btn-default btn" data-wysihtml5-command="justifyLeft" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-align-left"></i></a><a class="btn btn-default btn" data-wysihtml5-command="justifyCenter" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-align-center"></i></a><a class="btn btn-default btn" data-wysihtml5-command="justifyRight" title="CTRL+U" tabindex="-1" href="javascript:;" unselectable="on"><i class="fa fa-align-right"></i></a></div>
   </li>
</ul>

<script type="text/javascript">
$(function() {
	var wysihtmlEditor = new wysihtml5.Editor(document.querySelector('.wysihtml5'), {
		toolbar: 'wysihtml-toolbar',
		parserRules:  wysihtml5ParserRules,
		// TODO: Set a default set of options and allow override from site/CMS config
		// https://github.com/Voog/wysihtml/wiki/Configuration 
		// The following are actually the default values already
		handleTables: true,
		autoLink: true,
	});

	$('#wysihtml-forecolor').spectrum({
		change: function(color) {
			wysihtmlEditor.composer.commands.exec("foreColorStyle", color.toRgbString());
			$('#wysihtml-forecolor-value').val(color.toRgbString());
			return;
		},
	    //color: "#f00",
	    showAlpha: true,
	    showPalette: true,
	    showSelectionPalette: true,
	    containerClassName: 'sp-dark',
	    // TODO: Definitely make this something configurable so that it helps users out with their color scheme. Maybe comes from a JSON file even that helps it fit as part of a theme.
	    // May want to keep the auto add limited at this rate too.
	    // palette: [
	    //     ['black', 'white', 'blanchedalmond'],
	    //     ['rgb(255, 128, 0);', 'hsv 100 70 50', 'lightyellow']
	    // ]
	});

	// $('#wysihtml-forecolor').ColorPicker({
	// 	color: '#ffffff',
	// 	onShow: function (colpkr) {
	// 		$(colpkr).fadeIn(500);
	// 		return false;
	// 	},
	// 	onHide: function (colpkr) {
	// 		$(colpkr).fadeOut(500);
	// 		return false;
	// 	},
	// 	onChange: function (hsb, hex, rgb) {
	// 		console.dir(rgb);
	// 		var rgbString = "rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + ", 1);";
	// 		wysihtmlEditor.composer.commands.exec("foreColorStyle", rgbString);

	// 		//$('#wysihtml-forecolor-value').val(rgbString);
	// 		// $('#label-color div').css('backgroundColor', '#' + hex);
	// 		// $("#label-preview").css('color', '#' + hex);
	// 		// $("#label-color-input").val('#' + hex);
	// 	}
	// });
});


</script>