<?php
// Simply includes all of the scripts and styles necessary for the Medium editor clone. Rather than always being included in the layout template, 
// this can be included on an as needed basis. That way, non-admin users won't need to load the assets for nothing.
$this->blackprintAsset->style(array(
	'/bower_components/medium-editor/dist/css/medium-editor.min.css',
	// style override, medium editor breaks sassline by default
	//'/blackprint/css/medium-editor.css',
	'/bower_components/medium-editor/dist/css/themes/default.css',
	// The plugin itself
	//'/bower_components/medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css'
), array('inline' => false));

$this->blackprintAsset->script(array(
	'/bower_components/medium-editor/dist/js/medium-editor.min.js',
	'/bower_components/handlebars/handlebars.runtime.min.js',
	'/bower_components/jquery-sortable/source/js/jquery-sortable-min.js',
	// Unfortunately, jQuery File Upload Plugin has a few more dependencies itself
	'/bower_components/blueimp-file-upload/js/vendor/jquery.ui.widget.js',
	'/bower_components/blueimp-file-upload/js/jquery.iframe-transport.js',
	'/bower_components/blueimp-file-upload/js/jquery.fileupload.js',
	// The plugin itself
	'/bower_components/medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js',
	//'/blackprint/js/medium-editor-addons/heading3.js'
), array('inline' => false));
?>