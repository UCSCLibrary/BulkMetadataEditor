(function() {

var language = Omeka.BulkMetadataEditor.language;

jQuery(document).ready(function() {

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
	event.preventDefault();
	processItemRules();
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#bulk-metadata-editor-form").serialize(),
	    url: document.URL.split('?')[0]+"/index/items/max/15",
	    success: function(data)
            {   
		dataObj=[];
		if(data)
		    dataObj = jQuery.parseJSON(data);
		else
		    alert(language.CouldNotGeneratePreview);

		var r = new Array(), j=0;

		r[0] ='<tr><td class="prevcol1"><strong>' + language.Title + '</strong></td><td class="prevcol2"><strong>' + language.Description + '</strong></td><td class="prevcol3"><strong>' + language.ItemType + '</strong></td</tr>';

		for (var key=0, size=dataObj.length; key<size; key++){
		    r[++j] ='<tr><td>';
		    r[++j] = dataObj[key]['title'];
		    r[++j] = '</td><td class="whatever1">';
		    r[++j] = dataObj[key]['description'];
		    r[++j] = '</td><td class="whatever2">';
		    r[++j] = dataObj[key]['type'];
		    r[++j] = '</td></tr>';
		}

		jQuery('#itemPreviewDiv').html(r.join(""));

		jQuery("#show-more-items").click(showMoreItems);

            },
	    error: function(data,errorString,error) {
		if(errorstring=="timeout")
		    alert(language.ItemsPreviewRequestTooLong);
		else
		    alert(language.ErrorGeneratingPreview);

	    },
	    complete: function(data,status) {
		jQuery("#hide-item-preview").show();
		jQuery('#items-waiting').hide();
	    }
	});
	jQuery('#items-waiting').css('display:inline;');
    });


    jQuery("#hide-item-preview").click(function(event){
	event.preventDefault();
	jQuery('#itemPreviewDiv').html("<br>");	
	jQuery("#hide-item-preview").hide();
    });

    jQuery("#preview-fields-button").click(function(event){
	event.preventDefault();
	processItemRules();
	jQuery.ajax({
	    type: "POST",
	    timeout: 30000,
            data: jQuery("#bulk-metadata-editor-form").serialize(),
            url: document.URL.split('?')[0]+"/index/fields/max/7",
            success: function(data)
            {   
		dataObj=[];
		if(data)
		    dataObj = jQuery.parseJSON(data);
		else
		    alert(language.CouldNotGeneratePreview);

		var r = new Array(), j=-1;

		jQuery.each(dataObj,function(key,value) {
		    var title = value['title'];
		    delete value['title'];
		    r[++j] ='<tr><td><strong>';
		    if(key.indexOf("and corresponding fields") == -1)
			r[++j]="<strong>"+title+"</strong>";
		    else
			r[++j] = title;
		    r[++j] = '</strong></td></tr>';
		    jQuery.each(value,function(keyInner,valueInner){
			r[++j] ='<tr><td class="prevcol1">';
			r[++j] = valueInner['field'];
			r[++j] = '</td><td class="prevcol2">';
			r[++j] = valueInner['value'];
			r[++j] = '</td></tr>';
		    });
		});

		jQuery('#fieldPreviewDiv').html(r.join(""));

		jQuery("#show-more-fields").click(showMoreFields);

            },
	    error: function(data,errorString,error) {
		if(errorstring=="timeout")
		    alert(language.FieldsPreviewRequestTooLong);
		else
		    alert(language.ErrorGeneratingPreview);

	    },
	    complete: function(data,status) {
		jQuery("#hide-field-preview").show();
		jQuery('#fields-waiting').hide();
	    }
	});
	jQuery('#fields-waiting').css('display:inline;');
    });


    jQuery("#hide-field-preview").click(function(event){
	event.preventDefault();
	jQuery('#fieldPreviewDiv').html("<br>");	
	jQuery("#hide-field-preview").hide();
    });

    jQuery("#preview-changes-button").click(function(event){
	event.preventDefault();
	processItemRules();
	jQuery.ajax({
	    type: "POST",
	    timeout: 30000,
            data: jQuery("#bulk-metadata-editor-form").serialize(),
            url: document.URL.split('?')[0]+"/index/changes/max/20",
            success: function(data)
            {   
		dataObj=[];
		if(data)
		    dataObj = jQuery.parseJSON(data);
		else
		    alert(language.CouldNotGeneratePreview);

		var r = new Array(), j=0;

		r[0] ='<tr><td class="prevcol1"><strong>' + language.Item + '</strong></td><td class="prevcol2"><strong>' + language.Field + '</strong></td><td class="prevcol3"><strong>' + language.OldValue + '</strong></td><td class="prevcol4"><strong>' + language.NewValue + '</strong></td></tr>';

		for (var key=0, size=dataObj.length; key<size; key++){
		    r[++j] ='<tr><td class = "prevcol1">';
		    r[++j] = dataObj[key]['item'];
		    r[++j] = '</td><td class="prevcol2">';
		    r[++j] = dataObj[key]['field'];
		    r[++j] = '</td><td class="prevcol3">';
		    r[++j] = dataObj[key]['old'];
		    r[++j] = '</td><td class="prevcol4">';
		    r[++j] = dataObj[key]['new'];
		    r[++j] = '</td></tr>';
		}
		jQuery('#changesPreviewDiv').html(r.join(""));

		jQuery("#show-more-changes").click(showMoreChanges);

            },
	    error: function(data,errorString,error) {
		if(errorstring=="timeout")
		    alert(language.ChangesPreviewRequestTooLong);
		else
		    alert(language.ErrorGeneratingPreview);

	    },
	    complete: function(data,status) {
		jQuery('#changes-waiting').hide();
		jQuery("#hide-changes-preview").show();
	    }
	});
	jQuery('#changes-waiting').css('display','inline');
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
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
	type: "POST",
	timeout: 30000,
        data: jQuery("#bulk-metadata-editor-form").serialize(),
        url: document.URL.split('?')[0]+"/index/items/max/200",
        success: function(data)
        {   
	    dataObj=[];
	    if(data)
		dataObj = jQuery.parseJSON(data);
	    else
		alert(language.CouldNotGeneratePreview);

	    var r = new Array(), j=0;

	    r[0] ='<tr><td class="prevcol1"><strong>' + language.Title + '</strong></td><td class="prevcol2"><strong>' + language.Description + '</strong></td><td class="prevcol3"><strong>' + language.ItemType +'</strong></td></tr>';

	    for (var key=0, size=dataObj.length; key<size; key++){
		r[++j] ='<tr><td>';
		r[++j] = dataObj[key]['title'];
		r[++j] = '</td><td class="whatever1">';
		r[++j] = dataObj[key]['description'];
		r[++j] = '</td><td class="whatever2">';
		r[++j] = dataObj[key]['type'];
		r[++j] = '</td></tr>';
	    }
	    jQuery('#itemPreviewDiv').html(r.join(""));
	    
        }
    });
    jQuery("#hide-item-preview").show(300);

}

function showMoreFields(event){
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
	type: "POST",
	timeout: 30000,
        data: jQuery("#bulk-metadata-editor-form").serialize(),
        url: document.URL.split('?')[0]+"/index/fields/max/100",
        success: function(data)
        {   
	    dataObj=[];
	    if(data)
		dataObj = jQuery.parseJSON(data);
	    else
		alert(language.CouldNotGeneratePreview);

	    var r = new Array(), j=-1;

	    jQuery.each(dataObj,function(key,value) {
		var title = value['title'];
		delete value['title'];
		r[++j] ='<tr><td><strong>';
		if(key.indexOf("and corresponding fields") == -1)
		    r[++j]="<strong>"+title+"</strong>";
		else
		    r[++j] = title;
		r[++j] = '</strong></td></tr>';
		jQuery.each(value,function(keyInner,valueInner){
		    r[++j] ='<tr><td class="prevcol1">';
		    r[++j] = valueInner['field'];
		    r[++j] = '</td><td class="prevcol2">';
		    r[++j] = valueInner['value'];
		    r[++j] = '</td></tr>';
		});
	    });

	    jQuery('#fieldPreviewDiv').html(r.join(""));
	    
        }
    });
    
    jQuery("#hide-field-preview").show(300);
}


function showMoreChanges(event){
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
	type: "POST",
	timeout: 30000,
        data: jQuery("#bulk-metadata-editor-form").serialize(),
        url: document.URL.split('?')[0]+"/index/changes/max/200",
        success: function(data)
        {   
	    dataObj=[];
	    if(data)
		dataObj = jQuery.parseJSON(data);
	    else
		alert(language.CouldNotGeneratePreview);

	    var r = new Array(), j=0;

	    r[0] ='<tr><td class="prevcol1"><strong>' + language.Item + '</strong></td><td class="prevcol2"><strong>' + language.Field + '</strong></td><td class="prevcol3"><strong>' + language.OldValue + '</strong></td><td class="prevcol4"><strong>' + language.NewValue + '</strong></td></tr>';

	    for (var key=0, size=dataObj.length; key<size; key++){
		r[++j] ='<tr><td class = "prevcol1">';
		r[++j] = dataObj[key]['item'];
		r[++j] = '</td><td class="prevcol2">';
		r[++j] = dataObj[key]['field'];
		r[++j] = '</td><td class="prevcol3">';
		r[++j] = dataObj[key]['old'];
		r[++j] = '</td><td class="prevcol4">';
		r[++j] = dataObj[key]['new'];
		r[++j] = '</td></tr>';
	    }
	    jQuery('#changesPreviewDiv').html(r.join(""));
	    jQuery('#waiting').hide();
	    jQuery("#hide-changes-preview").show(300);
        }
    });
    jQuery('#waiting').show();
}

})();
