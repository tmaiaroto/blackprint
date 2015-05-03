<?php
use \lithium\net\http\Router;
// Only allow the draft action to include this editor.
if($this->request()->action == 'draft') {

// A save URL can be passed to this element to change the POST action. Otherwise, a default will be assumed (unless the $saveUrl is false which would prevent saving).
if(!isset($saveUrl)) {
	$saveUrl = Router::match(array('library' => $this->request()->library, 'controller' => $this->request()->controller, 'action' => 'update', 'admin' => true, 'args' => $this->request()->args, 'type' => 'json'));
}
$this->blackprintAsset->style(array(
	'/bower_components/x-editable/dist/jquery-editable/css/jquery-editable.css'
), array('inline' => false));

$this->blackprintAsset->script(array(
	'/bower_components/x-editable/dist/jquery-editable/js/jquery-editable-poshytip.min.js'
), array('inline' => false));

}
?>