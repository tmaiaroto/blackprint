<?php
use \lithium\net\http\Router;
// Only allow the draft action to include this editor.
//if($this->request()->action == 'draft' && isset($editorId)) {
if(isset($editorId)) {

// A save URL can be passed to this element to change the POST action. Otherwise, a default will be assumed (unless the $saveUrl is false which would prevent saving).
if(!isset($saveUrl)) {
	// TODO: This is gonna cause some issues. This means the medium editor can only be used on something that has a library and not the "main app"
	// Which is just a shell here. Blackprint is a library, but we have config and overrides at the root level which is also under revision control.
	// What needs to happen is all of Blackprint needs to be just a library and then get brought in to a new Li3 project with Composer.
	// So it can be as simple as "Composer install" -- possibly have an install script in this repo still that creates a whole new project for someone...
	// But the "main app" part would not be under revision control. That would be up to the user to maintain.
	// Routing needs to adjust too. I put a ticket in for this...For now, I'll just keep this app wrapper which basically requires people to fork Blackprint
	// rather than allowing people to use Blackprint with "any" Li3 project (route conflicts and such aside).
	// 
	$saveUrl = Router::match(array('library' => 'blackprint', 'controller' => $this->request()->controller, 'action' => 'update', 'admin' => true, 'args' => $this->request()->args, 'type' => 'json'));
	if($this->request()->library) {
		$saveUrl = Router::match(array('library' => $this->request()->library, 'controller' => $this->request()->controller, 'action' => 'update', 'admin' => true, 'args' => $this->request()->args, 'type' => 'json'));	
	}
}

if(!isset($config)) {
	$config = array();
}
$config += array(
	'firstHeader' => 'h1',
	'secondHeader' => 'h2',
	'thirdHeader' => 'heading3',
	'anchorTarget' => true,
	'buttonLabels' => array(
		'justifyLeft' => '<i class="fa fa-align-left"></i>',
		'justifyRight' => '<i class="fa fa-align-right"></i>',
		'justifyCenter' => '<i class="fa fa-align-center"></i>',
		'quote' => '<i class="fa fa-quote-left"></i>',
		'anchor' => '<i class="fa fa-link"></i>',
		'italic' => '<i class="fa fa-italic"></i>',
		'bold' => '<i class="fa fa-bold"></i>',
		'underline' => '<i class="fa fa-underline"></i>'
	)
);
$config = json_encode($config);
?>

<script type="text/javascript">
if(registeredMediumEditors === undefined) {
	var registeredMediumEditors = [];
}
$(function(){
	if($('#<?php echo $editorId; ?>').length) {

		var _<?php echo md5($editorId); ?> = {
			element: '<?php echo $editorId; ?>',
			editor: new MediumEditor('#<?php echo $editorId; ?>',<?php echo $config; ?>),
			field: $('#<?php echo $editorId; ?>').data('field'),
			dirty: false,
			insert: function() {
				$('#<?php echo $editorId; ?>').mediumInsert({
			        editor: this.editor,
			        cleanPastedHTML: true,
			        anchorTarget: true,
			        addons: {

			        }
			    });

				registeredMediumEditors.push(_<?php echo md5($editorId); ?>);

			    $('#<?php echo $editorId; ?>').on('input', function(){
			    	_<?php echo md5($editorId); ?>.dirty = true;
			    	// Save after a few seconds.
			    	setTimeout(function() {
			    		_<?php echo md5($editorId); ?>.save(_<?php echo md5($editorId); ?>.editor.serialize()["<?php echo $editorId; ?>"].value);
			    	}, 2000);
			    });
			},
			saveInProgress: false,
			saveUrl: '<?php echo $saveUrl; ?>',
			save: function(editorContent) {
				var data = <?php echo json_encode($document->data()); ?>;
				data[this.field] = editorContent;
				// Slow down the POST requests just a little bit. On window unload, do a final save as well, so data doesn't go missing and so we don't need a loop forever.
				if(!_<?php echo md5($editorId); ?>.saveInProgress) {
					_<?php echo md5($editorId); ?>.saveInProgress = true;
					$.ajax({
					  type: "POST",
					  url: _<?php echo md5($editorId); ?>.saveUrl,
					  data: data,
					  success: function(resp, status) {
					  	_<?php echo md5($editorId); ?>.saveInProgress = false;
					  	if(resp.document[this.field] == data[this.field]) {
					  		_<?php echo md5($editorId); ?>.dirty = false;
					  		// TODO: Send an event of some sort. Maybe fade in a check icon on the editable area or something. Perhaps a spinner while save() is running?
					  	}
					  },
					  always: function() {
					  	_<?php echo md5($editorId); ?>.saveInProgress = false;
					  },
					  dataType: 'json'
					});
				}
			}
		};

		_<?php echo md5($editorId); ?>.insert();

		var mediumSaveOnUnload = function() {
			var someonesDirty = false;
			for (var i = 0; i < registeredMediumEditors.length; i++) {
				setTimeout(function() {
		            setTimeout(function() {
		            	for (var j = 0; j < registeredMediumEditors.length; j++) {
		            		// Save everything after cancel (after 500 ms). The timeout within the timeout allows this to be executed after the cancel/"Don't reload" button is clicked.
		            		// Trying to save onbeforeunload results in very inconsistent, unpredictable, and confusing behavior.
							registeredMediumEditors[j].save(registeredMediumEditors[j].editor.serialize()[registeredMediumEditors[j].element].value);
		            	}
		            }, 500);
		        },1);
				
				if(registeredMediumEditors[i].dirty === true) {
					someonesDirty = true;
				}
			}

			if(someonesDirty) {
				return "Are you sure you want to leave this page? Your changes may not be saved if you leave too soon.";
			}
		 };
		 window.onbeforeunload = mediumSaveOnUnload;
	}
});
</script>
<?php } ?>