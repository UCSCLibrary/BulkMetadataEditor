jQuery(document).ready(function () {
    var $ = jQuery;
    var language = Omeka.BulkMetadataEditor.language;
    var url = document.URL.split('?')[0];

    jQuery("#changesRadio-replace-field").after(jQuery('#bulk-metadata-editor-replace-field'));
    jQuery("#changesRadio-replace-field").after(jQuery('#regexp-field'));
    jQuery("#changesRadio-replace-field").after(jQuery('#bulk-metadata-editor-search-field'));
    jQuery("#changesRadio-add-field").after(jQuery('#bulk-metadata-editor-add-field'));
    jQuery("#changesRadio-append-field").after(jQuery('#bulk-metadata-editor-append-field'));
    jQuery("#changesRadio-deduplicate-field").after(jQuery('#bulk-metadata-editor-deduplicate-field'));
    jQuery("#changesRadio-deduplicate-files-field").after(jQuery('#bulk-metadata-editor-deduplicate-files-field'));


    jQuery("#preview-items-button").wrap('<div class = "previewButtonDiv"></div>');
    jQuery("#preview-fields-button").wrap('<div class = "previewButtonDiv"></div>');
    jQuery("#preview-changes-button").wrap('<div class = "previewButtonDiv"></div>');

    jQuery("#preview-items-button").after('<div class="bulk-metadata-editor-waiting" id="items-waiting">' + language.PleaseWait + '</div>');
    jQuery("#preview-fields-button").after('<div class="bulk-metadata-editor-waiting" id="fields-waiting">' + language.PleaseWait + '</div>');
    jQuery("#preview-changes-button").after('<div class="bulk-metadata-editor-waiting" id="changes-waiting">' + language.PleaseWait + '</div>');

    jQuery("#preview-items-button").after(jQuery('#hide-item-preview'));
    jQuery("#preview-fields-button").after(jQuery('#hide-field-preview'));
    jQuery("#preview-changes-button").after(jQuery('#hide-changes-preview'));


    jQuery(".bulk-metadata-editor-selector").keypress(function(evt) {
	var key = evt.which;
	if(key == 13)  // the enter key code
	{
	    evt.preventDefault();
	}
    });


    jQuery(".bulk-metadata-editor-selector").focus(function(e) {
	var value = jQuery(this).val();
	if(value=="Input search term here") {
	    jQuery(this).val("");
	}
    });

   jQuery("#item-select-meta").change(function(){
       if(this.checked){
	   jQuery("#item-meta-selects").show();
       } else {
	   jQuery("#item-meta-selects").hide();
	   jQuery(".bulk-metadata-editor-selector").val("Input search term here");
       }
   });

    jQuery("#add-rule").on("click",function(event){
	event.preventDefault();
	jQuery("#item-rule-box").clone(true).appendTo("#item-rule-boxes");
    });

    jQuery("#changesRadio-replace").change(function(){
	if(this.checked) {
	    jQuery('#bulk-metadata-editor-search-field').show(300);
	    jQuery('#bulk-metadata-editor-replace-field').show(300);
	    jQuery('#regexp-field').show(300);
	    jQuery("#bulk-metadata-editor-add-field").hide(300);
	    jQuery("#bulk-metadata-editor-append-field").hide(300);
        jQuery("#bulk-metadata-editor-deduplicate-field").hide(300);
        jQuery("#bulk-metadata-editor-deduplicate-files-field").hide(300);
	}
    });
    jQuery("#changesRadio-add").change(function(){
	if(this.checked) {
	    jQuery('#bulk-metadata-editor-search-field').hide(300);
	    jQuery('#bulk-metadata-editor-replace-field').hide(300);
	    jQuery('#regexp-field').hide(300);
	    jQuery("#bulk-metadata-editor-add-field").show(300);
	    jQuery("#bulk-metadata-editor-append-field").hide(300);
	    jQuery("#bulk-metadata-editor-deduplicate-field").hide(300);
        jQuery("#bulk-metadata-editor-deduplicate-files-field").hide(300);
	}
    });
    jQuery("#changesRadio-append").change(function(){
	if(this.checked) {
	    jQuery('#bulk-metadata-editor-search-field').hide(300);
	    jQuery('#bulk-metadata-editor-replace-field').hide(300);
	    jQuery('#regexp-field').hide(300);
	    jQuery("#bulk-metadata-editor-add-field").hide(300);
	    jQuery("#bulk-metadata-editor-append-field").show(300);
	    jQuery("#bulk-metadata-editor-deduplicate-field").hide(300);
        jQuery("#bulk-metadata-editor-deduplicate-files-field").hide(300);
	}
    });
    jQuery("#changesRadio-deduplicate").change(function(){
        if(this.checked) {
            jQuery('#bulk-metadata-editor-search-field').hide(300);
            jQuery('#bulk-metadata-editor-replace-field').hide(300);
            jQuery('#regexp-field').hide(300);
            jQuery("#bulk-metadata-editor-add-field").hide(300);
            jQuery("#bulk-metadata-editor-append-field").hide(300);
            jQuery("#bulk-metadata-editor-deduplicate-field").show(300);
            jQuery("#bulk-metadata-editor-deduplicate-files-field").hide(300);
        }
    });
    jQuery("#changesRadio-deduplicate-files").change(function(){
        if(this.checked) {
            jQuery('#bulk-metadata-editor-search-field').hide(300);
            jQuery('#bulk-metadata-editor-replace-field').hide(300);
            jQuery('#regexp-field').hide(300);
            jQuery("#bulk-metadata-editor-add-field").hide(300);
            jQuery("#bulk-metadata-editor-append-field").hide(300);
            jQuery("#bulk-metadata-editor-deduplicate-field").hide(300);
            jQuery("#bulk-metadata-editor-deduplicate-files-field").show(300);
        }
    });
    jQuery("#changesRadio-delete").change(function(){
	if(this.checked) {
	    jQuery('#bulk-metadata-editor-search-field').hide(300);
	    jQuery('#bulk-metadata-editor-replace-field').hide(300);
	    jQuery('#regexp-field').hide(300);
	    jQuery("#bulk-metadata-editor-append-field").hide(300);
	    jQuery("#bulk-metadata-editor-add-field").hide(300);
	    jQuery("#bulk-metadata-editor-deduplicate-field").hide(300);
	}
    });

    jQuery("#preview-items-button").click(function(event){
        var max = 15;
        event.preventDefault();
        processItemRules();
        jQuery.ajax({
            url: url + '/index/items/max/' + max,
            dataType: 'json',
            data: jQuery('#bulk-metadata-editor-form').serialize(),
            timeout: 30000,
            success: function (data) {
                if (!data) {
                    alert(language.CouldNotGeneratePreview);
                } else {
                    var r = new Array(), j = 0;

                    r[j] = '<table><thead><tr><th scope="col">' + language.Title + '</th><th scope="col">' + language.Description + '</th><th scope="col">' + language.ItemType + '</th</tr></thead><tbody>';
                    for (var key = 0, size = data.length; key < size; key++) {
                        r[++j] ='<tr class="' + (key % 2 == 0 ? 'odd' : 'even') + '"><td>';
                        r[++j] = data[key]['title'];
                        r[++j] = '</td><td>';
                        r[++j] = data[key]['description'];
                        r[++j] = '</td><td>';
                        r[++j] = data[key]['type'];
                        r[++j] = '</td></tr>';
                    }
                    r[++j] = '</tbody></table>';

                    jQuery('#itemPreviewDiv').html(r.join(''));
                    jQuery('#show-more-items').click(showMoreItems);
                    jQuery('#hide-item-preview').show();
                }
            },
	    error: function(data,errorString,error) {
		if(errorstring=="timeout")
		    alert(language.ItemsPreviewRequestTooLong);
		else
		    alert(language.ErrorGeneratingPreview + "\n" + data.responseJSON);

	    },
	    complete: function(data,status) {
		jQuery('#items-waiting').hide();
	    }
	});
	jQuery('#items-waiting').css('display', 'inline');
    });


    jQuery("#hide-item-preview").click(function(event){
	event.preventDefault();
	jQuery('#itemPreviewDiv').html("<br>");	
	jQuery("#hide-item-preview").hide();
    });

    jQuery("#preview-fields-button").click(function(event){
        var max = 7;
        event.preventDefault();
        processItemRules();
        jQuery.ajax({
            url: url + '/index/fields/max/' + max,
            dataType: 'json',
            data: jQuery('#bulk-metadata-editor-form').serialize(),
            timeout: 30000,
            success: function (data) {
                if (!data) {
                    alert(language.CouldNotGeneratePreview);
                } else {
                    var r = new Array(), j = 0;

                    r[j] = '<table><tbody>';
                    jQuery.each(data, function (key, value) {
                        var title = value['title'];
                        delete value['title'];
                        r[++j] = '<tr class="even"><td colspan="2">';
                        r[++j] = title;
                        r[++j] = '</td></tr>';
                        jQuery.each(value, function (keyInner, valueInner) {
                            r[++j] ='<tr class="odd"><td>';
                            r[++j] = valueInner['field'];
                            r[++j] = '</td><td>';
                            r[++j] = valueInner['value'];
                            r[++j] = '</td></tr>';
                        });
                    });
                    r[++j] = '</tbody></table>';

                    jQuery('#fieldPreviewDiv').html(r.join(''));
                    jQuery('#show-more-fields').click(showMoreFields);
                    jQuery('#hide-field-preview').show();
                }
            },
	    error: function(data,errorString,error) {
		if(errorstring=="timeout")
		    alert(language.FieldsPreviewRequestTooLong);
		else
		    alert(language.ErrorGeneratingPreview + "\n" + data.responseJSON);

	    },
	    complete: function(data,status) {
		jQuery('#fields-waiting').hide();
	    }
	});
	jQuery('#fields-waiting').css('display', 'inline');
    });


    jQuery("#hide-field-preview").click(function(event){
	event.preventDefault();
	jQuery('#fieldPreviewDiv').html("<br>");	
	jQuery("#hide-field-preview").hide();
    });

    jQuery("#preview-changes-button").click(function(event){
        var max = 20;
        event.preventDefault();
        processItemRules();
        jQuery.ajax({
            url: url + '/index/changes/max/' + max,
            dataType: 'json',
            data: jQuery('#bulk-metadata-editor-form').serialize(),
            timeout: 30000,
            success: function (data) {
                if (!data) {
                    alert(language.CouldNotGeneratePreview);
                } else {
                    var r = new Array(), j = 0;

                    r[j] = '<table><thead><tr><th scope="col">' + language.Item + '</th><th>' + language.Field + '</th><th>' + language.OldValue + '</th><th>' + language.NewValue + '</th</tr></thead><tbody>';
                    for (var key = 0, size = data.length; key < size; key++) {
                        r[++j] ='<tr class="' + (key % 2 == 0 ? 'odd' : 'even') + '"><td>';
                        r[++j] = data[key]['item'];
                        r[++j] = '</td><td>';
                        r[++j] = data[key]['field'];
                        r[++j] = '</td><td>';
                        r[++j] = data[key]['old'];
                        r[++j] = '</td><td>';
                        r[++j] = data[key]['new'];
                        r[++j] = '</td></tr>';
                    }
                    r[++j] = '</tbody></table>';

                    jQuery('#changesPreviewDiv').html(r.join(''));
                    jQuery('#show-more-changes').click(showMoreChanges);
                    jQuery('#hide-changes-preview').show();
                }
            },
	    error: function(data,errorString,error) {
		if(errorstring=="timeout")
		    alert(language.ChangesPreviewRequestTooLong);
		else
		    alert(language.ErrorGeneratingPreview + "\n" + data.responseJSON);

	    },
	    complete: function(data,status) {
		jQuery('#changes-waiting').hide();
	    }
	});
	jQuery('#changes-waiting').css('display', 'inline');
    });



    jQuery("#hide-changes-preview").click(function(event){
	event.preventDefault();
	jQuery('#changesPreviewDiv').html("<br>");	
	jQuery("#hide-changes-preview").hide();
    });

    jQuery(".removeRule").click(function(){
	if(jQuery(".item-rule-box").length > 1)
	    jQuery(this).parent().remove();	
	else
	    jQuery("#item-select-meta").trigger('click');
    });

});


function processItemRules(){

    jQuery(".hiddenField").remove();

    jQuery(".bulk-metadata-editor-element-id").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-rule-elements[]" value='+jQuery(this).val()+' />';
	jQuery('form').append(html);
    });

    jQuery(".bulk-metadata-editor-compare").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-compare-types[]" value='+jQuery(this).val()+' />';
	jQuery('form').append(html);
    });

    jQuery(".bulk-metadata-editor-case").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-cases[]" value='+jQuery(this).prop('checked')+' />';
	jQuery('form').append(html);
    });

    jQuery(".bulk-metadata-editor-selector").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-selectors[]" value="'+jQuery(this).val()+'" />';
	jQuery('form').append(html);
    });

}

function showMoreItems(event){
    var max = 200;
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
        url: url + '/index/items/max/' + max,
        dataType: 'json',
        data: jQuery('#bulk-metadata-editor-form').serialize(),
        timeout: 30000,
        success: function (data) {
            if (!data) {
                alert(language.CouldNotGeneratePreview);
            } else {
                var r = new Array(), j = 0;

                r[0] = '<table><thead><tr><th scope="col">' + language.Title + '</th><th scope="col">' + language.Description + '</th><th scope="col">' + language.ItemType + '</th</tr></thead><tbody>';
                for (var key = 0, size = data.length; key < size; key++) {
                    r[++j] ='<tr class="' + (key % 2 == 0 ? 'odd' : 'even') + '"><td>';
                    r[++j] = data[key]['title'];
                    r[++j] = '</td><td>';
                    r[++j] = data[key]['description'];
                    r[++j] = '</td><td>';
                    r[++j] = data[key]['type'];
                    r[++j] = '</td></tr>';
                }
                r[++j] = '</tbody></table>';

                jQuery('#itemPreviewDiv').html(r.join(''));
            }
        }
    });
}

function showMoreFields(event){
    var max = 100;
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
        url: url + '/index/fields/max/' + max,
        dataType: 'json',
        data: jQuery('#bulk-metadata-editor-form').serialize(),
        timeout: 30000,
        success: function (data) {
            if (!data) {
                alert(language.CouldNotGeneratePreview);
            } else {
                var r = new Array(), j = 0;

                r[j] = '<table><tbody>';
                jQuery.each(data, function (key, value) {
                    var title = value['title'];
                    delete value['title'];
                    r[++j] = '<tr class="even"><td colspan="2">';
                    r[++j] = title;
                    r[++j] = '</td></tr>';
                    jQuery.each(value, function (keyInner, valueInner) {
                        r[++j] ='<tr class="odd"><td>';
                        r[++j] = valueInner['field'];
                        r[++j] = '</td><td>';
                        r[++j] = valueInner['value'];
                        r[++j] = '</td></tr>';
                    });
                });
                r[++j] = '</tbody></table>';

                jQuery('#fieldPreviewDiv').html(r.join(''));
            }
        }
    });
}


function showMoreChanges(event){
    var max = 200;
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
        url: url + '/index/changes/max/' + max,
        dataType: 'json',
        data: jQuery('#bulk-metadata-editor-form').serialize(),
        timeout: 30000,
        success: function (data) {
            if (!data) {
                alert(language.CouldNotGeneratePreview);
            } else {
                var r = new Array(), j = 0;

                r[j] = '<table><thead><tr><th scope="col">' + language.Item + '</th><th>' + language.Field + '</th><th>' + language.OldValue + '</th><th>' + language.NewValue + '</th</tr></thead><tbody>';
                for (var key = 0, size = data.length; key < size; key++) {
                    r[++j] ='<tr class="' + (key % 2 == 0 ? 'odd' : 'even') + '"><td>';
                    r[++j] = data[key]['item'];
                    r[++j] = '</td><td>';
                    r[++j] = data[key]['field'];
                    r[++j] = '</td><td>';
                    r[++j] = data[key]['old'];
                    r[++j] = '</td><td>';
                    r[++j] = data[key]['new'];
                    r[++j] = '</td></tr>';
                }
                r[++j] = '</tbody></table>';

                jQuery('#changesPreviewDiv').html(r.join(''));
            }
        }
    });
}

})();
