
jQuery(document).ready(function() {

   jQuery("#item-select-all").change(function(){
       if(this.checked){
	   jQuery("#item-select-collection").attr("checked",false);
	   jQuery("#item-select-meta").attr("checked",false);
	   jQuery("#item-collection-select").hide(300);
	   jQuery("#item-meta-selects").hide(300);
	   jQuery("#item-select-collection").attr('disabled','disabled');
	   jQuery("#item-select-meta").attr('disabled','disabled');
       } else {
	   jQuery("#item-select-collection").removeAttr('disabled');
	   jQuery("#item-select-meta").removeAttr('disabled');
       }
   });

    jQuery("#item-select-collection").change(function(){
       if(this.checked){
	   jQuery("#item-select-all").attr("checked",false);
	   jQuery("#item-collection-select").show(300);
       } else {
	   jQuery("#item-collection-select").hide(300);	   
       }
   });

   jQuery("#item-select-meta").change(function(){
       if(this.checked){
	   jQuery("#item-meta-selects").show(300);
       } else {
	   jQuery("#item-meta-selects").hide(300);
       }
   });

   jQuery("#field-select-all").on("click",function(){
       jQuery("#field-select-list").hide(300);
   });

   jQuery("#field-select-some").on("click",function(){
       jQuery("#field-select-list").show(300);
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
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#sedmeta-form").serialize(),
            url: document.URL.split('?')[0]+"/index/items",
            success: function(data)
            {   
		dataObj = jQuery.parseJSON(data);
		var r = new Array(), j=-1;
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

    });

    jQuery("#hide-item-preview").click(function(){
	jQuery('#item-preview').html("<br>");	
	jQuery("#hide-item-preview").hide(300);
    });

    jQuery("#preview-fields-button").click(function(event){
	event.preventDefault();
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#sedmeta-form").serialize(),
            url: document.URL.split('?')[0]+"/index/fields",
            success: function(data)
            {   
		dataObj = jQuery.parseJSON(data);
		var r = new Array(), j=-1;
		jQuery.each(dataObj,function(key,value) {
		    r[++j] ='<tr><td>';
		    r[++j] = key;
		    r[++j] = '</td></tr>';
		    jQuery.each(value,function(keyInner,valueInner){
			r[++j] ='<tr><td class="whatever1">';
			r[++j] = valueInner['field'];
			r[++j] = '</td><td class="whatever2">';
			r[++j] = valueInner['value'];
			r[++j] = '</td></tr>';
		    });
		});

		jQuery('#field-preview').html(r.join(""));
		
            }
	});
	
	jQuery("#hide-field-preview").show(300);
    });

    jQuery("#hide-field-preview").click(function(){
	jQuery('#field-preview').html("<br>");	
	jQuery("#hide-field-preview").hide(300);
    });

    jQuery("#preview-changes-button").click(function(event){
	event.preventDefault();
	jQuery.ajax({
	    type: "POST",
            data: jQuery("#sedmeta-form").serialize(),
            url: document.URL.split('?')[0]+"/index/changes",
            success: function(data)
            {   
		//jQuery('#changes-preview').html(data);
		dataObj = jQuery.parseJSON(data);
		var r = new Array(), j=-1;
		for (var key=0, size=dataObj.length; key<size; key++){
		    r[++j] ='<tr><td>';
		    r[++j] = dataObj[key]['item'];
		    r[++j] = '</td><td class="whatever1">';
		    r[++j] = dataObj[key]['field'];
		    r[++j] = '</td><td class="whatever2">';
		    r[++j] = dataObj[key]['old'];
		    r[++j] = '</td><td class="whatever3">';
		    r[++j] = dataObj[key]['new'];
		    r[++j] = '</td></tr>';
		}

		jQuery('#changes-preview').html(r.join(""));
            }
	});
	jQuery("#hide-changes-preview").show(300);
    });

    jQuery("#hide-changes-preview").click(function(){
	jQuery('#changes-preview').html("<br>");	
	jQuery("#hide-changes-preview").hide(300);
    });

});
