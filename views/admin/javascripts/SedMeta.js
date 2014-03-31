
jQuery(document).ready(function() {
   jQuery("#select-item-all").on("click",function(){
       jQuery("#sedmeta-select-items").hide();
   });
   jQuery("#select-item-some").on("click",function(){
       jQuery("#sedmeta-select-items").show();
   });
   jQuery("#select-field-all").on("click",function(){
       jQuery("#sedmeta-select-fields").hide();
   });
   jQuery("#select-field-some").on("click",function(){
       jQuery("#sedmeta-select-fields").show();
   });
});

