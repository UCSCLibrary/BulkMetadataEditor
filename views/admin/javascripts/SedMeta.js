jQuery(document).ready(function() {

    jQuery(".sedmeta-selector").keypress(function(evt) {
	var key = evt.which;
	if(key == 13)  // the enter key code
	{
	    evt.preventDefault();
	}
    });

    jQuery(".sedmeta-selector").focus(function(e) {
	var value = jQuery(this).val();
	if(value=="Input search term here") {
	    jQuery(this).val("");
	}
    });

   jQuery("#item-select-meta").change(function(){
       if(this.checked){
	   jQuery("#item-meta-selects").show(300);
       } else {
	   jQuery("#item-meta-selects").hide(300);
       }
   });

    jQuery("#add-rule").on("click",function(event){
	event.preventDefault();
	var newbox = "<h2>hello world</h2>";
	jQuery("#item-rule-box").clone(true).appendTo("#item-rule-boxes");
    });

    jQuery("#changes-replace-radio").change(function(){
	if(this.checked) {
	    jQuery("#changes-replace").show(300);
	    jQuery("#changes-append").hide(300);
	    jQuery("#changes-add").hide(300);
	}
    });
    jQuery("#changes-add-radio").change(function(){
	if(this.checked) {
	    jQuery("#changes-replace").hide(300);
	    jQuery("#changes-append").hide(300);
	    jQuery("#changes-add").show(300);
	}
    });
    jQuery("#changes-append-radio").change(function(){
	if(this.checked) {
	    jQuery("#changes-replace").hide(300);
	    jQuery("#changes-append").show(300);
	    jQuery("#changes-add").hide(300);
	}
    });
    jQuery("#changes-delete-radio").change(function(){
	if(this.checked) {
	    jQuery("#changes-replace").hide(300);
	    jQuery("#changes-append").hide(300);
	    jQuery("#changes-add").hide(300);
	}
    });

    jQuery("#preview-items-button").click(function(event){
	event.preventDefault();
	processItemRules();
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#sedmeta-form").serialize(),
            url: document.URL.split('?')[0]+"/index/items/max/15",
            success: function(data)
            {   
		dataObj = jQuery.parseJSON(data);
		var r = new Array(), j=0;

		r[0] ='<tr><td class="prevcol1"><strong>Title</strong></td><td class="prevcol2"><strong>Description</strong></td><td class="prevcol3"><strong>Item Type</strong></td</tr>';

		for (var key=0, size=dataObj.length; key<size; key++){
		    r[++j] ='<tr><td>';
		    r[++j] = dataObj[key]['title'];
		    r[++j] = '</td><td class="whatever1">';
		    r[++j] = dataObj[key]['description'];
		    r[++j] = '</td><td class="whatever2">';
		    r[++j] = dataObj[key]['type'];
		    r[++j] = '</td></tr>';
		}

		jQuery('#item-preview').html(r.join(""));

		jQuery("#show-more-items").click(showMoreItems);
		
            }
	});
	jQuery("#hide-item-preview").show(300);

    });


    jQuery("#hide-item-preview").click(function(event){
	event.preventDefault();
	jQuery('#item-preview').html("<br>");	
	jQuery("#hide-item-preview").hide();
    });

    jQuery("#preview-fields-button").click(function(event){
	event.preventDefault();
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#sedmeta-form").serialize(),
            url: document.URL.split('?')[0]+"/index/fields/max/7",
            success: function(data)
            {   
		dataObj = jQuery.parseJSON(data);
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

		jQuery('#field-preview').html(r.join(""));

		jQuery("#show-more-fields").click(showMoreFields);
		
            }
	});
	
	jQuery("#hide-field-preview").show(300);
    });


    jQuery("#hide-field-preview").click(function(event){
	event.preventDefault();
	jQuery('#field-preview').html("<br>");	
	jQuery("#hide-field-preview").hide();
    });

    jQuery("#preview-changes-button").click(function(event){
	event.preventDefault();
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#sedmeta-form").serialize(),
            url: document.URL.split('?')[0]+"/index/changes/max/20",
            success: function(data)
            {   
		dataObj = jQuery.parseJSON(data);
		//console.log(dataObj);
		var r = new Array(), j=0;

		r[0] ='<tr><td class="prevcol1"><strong>Item</strong></td><td class="prevcol2"><strong>Field</strong></td><td class="prevcol3"><strong>Old Value</strong></td><td class="prevcol4"><strong>New Value</strong></td></tr>';

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
		jQuery('#changes-preview').html(r.join(""));

		jQuery("#show-more-changes").click(showMoreChanges);

		jQuery('#waiting').hide();
		jQuery("#hide-changes-preview").show(300);
            }
	});
	jQuery('#waiting').show();
    });



    jQuery("#hide-changes-preview").click(function(event){
	event.preventDefault();
	jQuery('#changes-preview').html("<br>");	
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

    jQuery(".sedmeta-element-id").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-rule-elements[]" value='+jQuery(this).val()+' />';
	jQuery('form').append(html);
    });

    jQuery(".sedmeta-compare").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-compare-types[]" value='+jQuery(this).val()+' />';
	jQuery('form').append(html);
    });

    jQuery(".sedmeta-case").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-cases[]" value='+jQuery(this).prop('checked')+' />';
	jQuery('form').append(html);
    });

    jQuery(".sedmeta-selector").each(function(index){
	var html = '<input class="hiddenField" type=hidden name="item-selectors[]" value='+jQuery(this).val()+' />';
	jQuery('form').append(html);
    });

    jQuery("input:text").keypress(function(){
	alert('clicked');
    });

}

function showMoreItems(event){
    event.preventDefault();
    processItemRules();
    jQuery.ajax({
	type: "POST",
        data: jQuery("#sedmeta-form").serialize(),
        url: document.URL.split('?')[0]+"/index/items/max/200",
        success: function(data)
        {   
	    dataObj = jQuery.parseJSON(data);
	    var r = new Array(), j=0;

	    r[0] ='<tr><td class="prevcol1"><strong>Title</strong></td><td class="prevcol2"><strong>Description</strong></td><td class="prevcol3"><strong>Item Type</strong></td</tr>';

	    for (var key=0, size=dataObj.length; key<size; key++){
		r[++j] ='<tr><td>';
		r[++j] = dataObj[key]['title'];
		r[++j] = '</td><td class="whatever1">';
		r[++j] = dataObj[key]['description'];
		r[++j] = '</td><td class="whatever2">';
		r[++j] = dataObj[key]['type'];
		r[++j] = '</td></tr>';
	    }

	    jQuery('#item-preview').html(r.join(""));
	    
        }
    });
    jQuery("#hide-item-preview").show(300);

}

function showMoreFields(event){
    event.preventDefault();
    jQuery.ajax({
	type: "POST",
        data: jQuery("#sedmeta-form").serialize(),
        url: document.URL.split('?')[0]+"/index/fields/max/100",
        success: function(data)
        {   
	    dataObj = jQuery.parseJSON(data);
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

	    jQuery('#field-preview').html(r.join(""));
	    
        }
    });
    
    jQuery("#hide-field-preview").show(300);
}


function showMoreChanges(event){
    event.preventDefault();
    jQuery.ajax({
	type: "POST",
        data: jQuery("#sedmeta-form").serialize(),
        url: document.URL.split('?')[0]+"/index/changes/max/200",
        success: function(data)
        {   
	    dataObj = jQuery.parseJSON(data);
	    //console.log(dataObj);
	    var r = new Array(), j=0;

	    r[0] ='<tr><td class="prevcol1"><strong>Item</strong></td><td class="prevcol2"><strong>Field</strong></td><td class="prevcol3"><strong>Old Value</strong></td><td class="prevcol4"><strong>New Value</strong></td></tr>';

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
	    jQuery('#changes-preview').html(r.join(""));
	    jQuery('#waiting').hide();
	    jQuery("#hide-changes-preview").show(300);
        }
    });
    jQuery('#waiting').show();
}
