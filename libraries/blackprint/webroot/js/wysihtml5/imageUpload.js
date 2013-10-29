$(function() {
	$('.wysihtml5').wysihtml5('deepExtend', {
		stylesheets: ['/bower_components/bootstrap/dist/css/bootstrap.min.css', '/blackprint/css/site.css'],
		toolbar: {
				speech: '<li>' +
							'<div class="bootstrap-wysihtml5-insert-image-modal modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><a class="close" data-dismiss="modal">×</a><h3 class="modal-title">Insert image</h3></div><div class="modal-body"><input value="http://" class="bootstrap-wysihtml5-insert-image-url form-control"></div><div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a><a href="#" class="btn btn-primary" data-dismiss="modal">Insert image</a></div></div></div></div>' +
								'<a class="btn btn-default" data-wysihtml5-command="insertSpeech" title="Voice input" href="javascript:;" unselectable="on"><i class="fa fa-microphone"></i></a>' +
							'</div>' +
						'</li>',
				code:  function(locale, options) {
						return '<li><a class="btn btn-default" data-wysihtml5-command="formatInline" data-wysihtml5-command-value="code" href="javascript:;" unselectable="on"><i class="fa fa-th-large"></i></li>'
				},
				insertThis: function(locale, options) {
						return '<li><a class="btn btn-default" data-wysihtml5-command="fomratInline" data-wysihtml5-command-value="span" href="javascript:;"><i class="fa fa-check"></i></a></li>';
				},
				insertAnything:  function(locale, options) {
								return '<li>' +
										'<a class="btn" data-wysihtml5-command="insertHTML" href="javascript:;" data-toggle="modal" data-target="#insertAnythingModal" unselectable="on"><i class="fa fa-asterisk"></i></a>' +
										'<div id="insertAnythingModal" data-wysihtml5-dialog="insertHTML" class="modal hide fade">' +
												'<div class="modal-header">' +
														'<a class="close" data-dismiss="modal">&times;</a>' +
														'<h3>Insert Some Stuff</h3>' +
												'</div>' +
												'<div class="modal-body">' +
														'<textarea id="myJazz"></textarea>' +
												'</div>' +
												'<div class="modal-footer">' +
														'<a class="btn" href="javascript:;" data-dismiss="modal">Cancel</a>' +
														'<a class="btn btn-primary" data-dismiss="modal" data-wysihtml5-command="insertHTML" onClick="$(this).attr(\'data-wysihtml5-command-value\', $(\'#myJazz\').val()); $(\'#myJazz\').val(\'\')" data-wysihtml5-command-value="jazz" href="javascript:;" unselectable="on">Insert</a>' +
												'</div>' +
										'</div>' +
								'</li>';
				},
		},
		html: true,
		parserRules: {
				classes: {
				  "middle": 1,
				  "icon-beer": 1,
				  "prettyprint": 1
				},
				tags: {
						// <iframe width="560" height="315" src="http://www.youtube.com/embed/eE_IUPInEuc" frameborder="0" allowfullscreen></iframe>
						iframe: {
								allow_attributes: ['height', 'width', 'src', 'frameborder', 'allowfullscreen']
						},
						code: {
								allow_attributes: ['data-language', 'style']
						},
						pre: {
								allow_attributes: ['style']
						},
						strong: {},
						em: {},
						i: {}
				}
		}
	});

	// Hack for inserting code I believe... May not be necessary.
	var wysihtml5Editor = $('.wysihtml5').data("wysihtml5").editor;
	//console.dir(wysihtml5Editor)
	wysihtml5Editor.on('blur', function(a) {
		$('.wysihtml5').val(wysihtml5Editor.getValue());
	})
	
});