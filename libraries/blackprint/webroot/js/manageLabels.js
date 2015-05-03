$(document).ready(function(){
	listLabels();
	
	// Apply labels to current blog post
	$('#current-labels').delegate('.apply-label', 'click', function() {
		if($(this).hasClass('label-selected')) {
			// $(this).removeClass('label-selected');
			// $('#PostLabel' + $(this).attr('rel')).remove();
			var $selectedLabel = $(this);
			var labelId = $(this).attr('rel');
			//$(this).addClass('label-selected');
			applyLabel(labelId, true, function(data){
				if(data.success) {
					$selectedLabel.removeClass('label-selected');
					var updateElements = $('#labels-update-elements').val().split(' ');
					for(var i in updateElements) {
						$(updateElements[i]).find('[data-label-id="'+labelId+'"]').parent('a').remove();
					}
				}
			});
		} else {
			// $("#PostLabelsInputs").append('<input type="hidden" name="labels[]" id="PostLabel' + $(this).attr('rel') + '" class="applied-post-label" value="' + $(this).attr('rel') + '" />');
			// AJAX request now because of live edit in place.
			
			var $selectedLabel = $(this);
			var labelId = $(this).attr('rel');
			var labelsIndexUrl = $('#labels-index-url').val();
			//$(this).addClass('label-selected');
			applyLabel(labelId, false, function(data){
				if(data.success) {
					$selectedLabel.addClass('label-selected');
					var updateElements = $('#labels-update-elements').val().split(' ');
					if(data.label){
						for(var i in updateElements) {
							if($(updateElements[i] + 'a').length > 0) {
								$(updateElements[i] + ' a:last-child').after('<a href="'+ labelsIndexUrl + '/' + encodeURIComponent(data.label.name) + '" style="text-decoration:none;"><span class="label" data-label-id="'+labelId+'" style="color: ' + data.label.color + '; background-color: ' + data.label.bgColor + '">'+ data.label.name +'</span></a>');
							} else {
								// append leaves a space if there's other items, but a:last-child doesn't work if there aren't any items yet
								$(updateElements[i]).append('<a href="'+ labelsIndexUrl + '/' + encodeURIComponent(data.label.name) + '" style="text-decoration:none;"><span class="label" data-label-id="'+labelId+'" style="color: ' + data.label.color + '; background-color: ' + data.label.bgColor + '">'+ data.label.name +'</span></a>');
							}
						}
					}
				}
			});
		}
		
	});
	
	// Manage existing labels
	$('#labels-mode').delegate('#manage-existing-labels', 'click', function() {
	//$('#manage-existing-labels').on('click', function() {
		listLabels(true);
	});
	
	// Flip back to apply labels
	$('#labels-mode').delegate('#apply-existing-labels', 'click', function() {
	//$('#apply-existing-labels').on('click', function() {
		listLabels();
		$("#new-label-name").val('');
		$(".label-colors").hide();
		$('#label-preview').text('Label Preview');
	});
	
	// Add a new label (or overwrite an existing one)
	// First, setup the color pickers.
	$('#label-color').ColorPicker({
		color: '#ffffff',
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#label-color div').css('backgroundColor', '#' + hex);
			$("#label-preview").css('color', '#' + hex);
			$("#label-color-input").val('#' + hex);
		}
	});

	$('#label-bg-color').ColorPicker({
		color: '#0000ff',
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#label-bg-color div').css('backgroundColor', '#' + hex);
			$("#label-preview").css('backgroundColor', '#' + hex);
			$("#label-bg-color-input").val('#' + hex);
		}
	});
	
	// Clicking on the input field to add a new label will show the options for the label and the submit button.
	$("#new-label-name").click(function() {
		$(".label-colors").fadeIn(500);
	});
	
	// Hide the options on blur if there was no label name entered in the input field
	$("#new-label-name").blur(function() {
		if($("#new-label-name").val().length == 0) {
			//$(".label-colors").hide();
			//$(".label-colors").fadeOut(500); // can't, when picking a color this blurs and we want to show the preview still even if no label name has been entered yet.
			// it is possible a user will choose a color first...
		}
	});
	
	// Show a live preview.
	$("#new-label-name").on('keyup', function() {
		if($("#new-label-name").val().length == 0) {
			$("#label-preview").text('Label Preview');
		} else {
			$("#label-preview").text($("#new-label-name").val());
		}
	});

});

function saveLabel() {
	if($("#new-label-name").val().length == 0) {
		// TODO: Show error message?
		return false;
	}
	
	// Do not allow labels longer than 40 characters
	if($("#new-label-name").val().length > 40) {
		// TODO: Show error message? 
		return false;
	} else {
		var saveUrl = '/admin/blackprint/labels/create.json';
		oldLabel = $('#label-being-edited').val();
		if(oldLabel) {
			saveUrl = '/admin/blackprint/labels/update/' + encodeURIComponent(oldLabel) + '.json';
		}
		$.ajax({
			type: 'POST',
			url: saveUrl,
			data: $("#create-new-label").serialize(),
			success: function(data) {
				if(data.success == true) {
					listLabels();
					$("#new-label-name").val('');
					$(".label-colors").hide();
					$('#label-preview').text('Label Preview');
				}
				//refresh the page
				//window.location.reload();
			}
		});
	}
}

/**
* Lists all labels.
* Allows the user to apply labels to the blog post
* or manage the label settings including the deletion
* of labels through the entire system.
* 
* Deleted labels are not removed from each blog post
* immediately. The next time the post is loaded, it
* will clean up missing labels.
* 
* @param boolean manage
*/
function listLabels(manage) {
	//$("#current-labels").fadeOut('medium', function() {
	$("#current-labels").slideUp('medium', function() {
		$.get('/admin/blackprint/labels/index.json', function(data) {
			if(data.success == true) {
				var labelHtml = '';
				if(manage == true) {
					// Manage labels
					$('#labels-mode').html('<a href="#" class="small" id="apply-existing-labels">apply labels to post</a>');
						labelHtml += '<input type="hidden" id="label-being-edited" value="" />';
					for(i in data.labels) {
						labelHtml += '<div class="manage-label-wrapper"><a href="javascript:editLabel(\'' + data.labels[i].name + '\', \'' + data.labels[i].color + '\', \'' + data.labels[i].bgColor + '\');" style="text-decoration: none;" rel="' + data.labels[i]._id + '" class="manage-label" id="label-' + data.labels[i].name.replace(/\s/g, '-') + '"><span class="label" style="background: ' + data.labels[i].bgColor + '; color: ' + data.labels[i].color + ';">' + data.labels[i].name + '</span></a><br style="clear: left;" /><a href="javascript:editLabel(\'' + data.labels[i].name + '\', \'' + data.labels[i].color + '\', \'' + data.labels[i].bgColor + '\');" class="edit-label"><i class="fa fa-pencil"></i>Edit</a><a href="javascript:deleteLabel(\'' + data.labels[i].name + '\');" class="delete-label" onClick="return confirm(\'Are you sure you want to completely delete this label? It will be removed from all blog posts.\');"><i class="fa fa-trash"></i>Delete</a></div>';
					}
				} else {
					// Apply labels
					$('#labels-mode').html('<a href="#" class="small" id="manage-existing-labels">manage existing labels</a>');
					for(i in data.labels) {
						var selectedClass = '';
						if($('.applied-post-label[value="' + data.labels[i]._id + '"]').val() == data.labels[i]._id) {
							selectedClass = ' label-selected';
						}
						labelHtml += '<a href="#" style="text-decoration: none;" rel="' + data.labels[i]._id + '" class="apply-label ' + selectedClass + '" id="label-' + data.labels[i].name.replace(/\s/g, '-') + '"><span class="label" style="background: ' + data.labels[i].bgColor + '; color: ' + data.labels[i].color.replace(/\s/g, '-') + ';">' + data.labels[i].name + '</span></a>';
					}
					
				}
				$("#current-labels").html(labelHtml);
				//$("#current-labels").fadeIn('medium');
				$("#current-labels").slideDown('medium');
				return true;
			} else {
				return false;
			}
		});

	});
}

function editLabel(label, color, bgColor) {
	$('#label-being-edited').val(label); // keep track of this to pass to the back-end so we know what to update
	$("#new-label-name").val(label);
	$("#new-label-name").focus();
	$("#label-preview").text(label);
	$(".label-colors").show();
	$('#label-color').ColorPickerSetColor(color);
	$('#label-chosen-color').css('background-color', color);
	$('#label-preview').css('color', color);
	$("#label-color-input").val(color);
	$('#label-bg-color').ColorPickerSetColor(bgColor);
	$('#label-chosen-bg-color').css('background-color', bgColor);
	$('#label-preview').css('background-color', bgColor);
	$("#label-bg-color-input").val(bgColor);
}

function deleteLabel(label) {
	$.ajax({
		type: 'POST',
		url: '/admin/blackprint/labels/delete/' + label + '.json',
		data: {},
		success: function(data) {
			if(data.success == true) {
				$('#PostLabel' + data._id).remove();
				listLabels(true);
			}
		}
	});
}

function applyLabel(label, remove, callback) {
	callback === undefined ? function(){}:callback;
	remove === undefined ? false:remove;
	var docId = $('#labels-document-id').val();
	if(docId !== undefined && docId !== "") {
		var applyLabelUrl = '/admin/blackprint/posts/apply_label/' + docId + '/' + label + '.json';
		if(remove === true) {
			applyLabelUrl = '/admin/blackprint/posts/apply_label/' + docId + '/' + label + '/1.json';
		}
		$.ajax({
			type: 'GET',
			url: applyLabelUrl,
			data: {},
			success: callback
		});
	}
}