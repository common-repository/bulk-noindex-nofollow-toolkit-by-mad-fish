//Toggle on/off the noindex or nofollow options
jQuery(".bnitk-mfd-toggle").change(function(b) {
    var status = '';
    var result_chk = 0;
   
    if(this.checked) {        
        result_chk = 1;
    }
    if(jQuery(this).attr('rel') != ''){
	   switch(jQuery(this).attr('rel')) {
		  case 'cats':
		    var action = 'update_cat_callback'
		    break;
		  case 'page':
		    var action = 'update_page_callback'
		    break;
		  default:
		    var action = 'update_page_callback'
		}
	}
	    

	var data = {
		action: action,
		post_id: this.value,
		check_class: jQuery(this).attr('name'),
		nonce: jQuery("input[name=nonce]").val(),
		result: result_chk 
	};	
	
	jQuery.post( ajaxurl, data, function(response) {
	    // handle response from the AJAX request.
	    var resp = jQuery.parseJSON(response)	    

		//let the user know that the posts are updated
		var upd_class = 'updated'
	    if(resp.status != 'OK'){
	    	upd_class = 'error'	
	    }

	    var notification_response = '<div class="'+upd_class+' notice"><p>'+resp.msg+'</p></div>';
	    jQuery('.request-notification').html(notification_response);
	    
	});
});

//functionality to Check all visible posts/pages in the table
jQuery(document).on('change', '#cb-select-all', function(c) {
	if(this.checked) {   
		jQuery('.cb-post').attr("checked", "checked");
    }else{
		jQuery('.cb-post').attr("checked", null);
    }
	
});

//Support for bulk updating the posts
jQuery( "#bulk-update" ).submit(function( event ) {
    
	 if(jQuery(this).attr('rel') != ''){
	   switch(jQuery(this).attr('rel')) {
		  case 'catForm':
		    var action = 'update_cat_bulk_callback';
		    var itemTyp = 'terms';
		    break;
		  case 'pageForm':
		    var action = 'update_page_bulk_callback';
		    var itemTyp = 'pages';
		    break;
		  default:
		    var action = 'update_page_bulk_callback';
		    var itemTyp = 'pages';
		}
	}

	

  	var selPostIds = new Array();

	jQuery("input.cb-post:checked").each(function() {
       selPostIds.push(jQuery(this).val());
    });
	
	var directive_val = jQuery('#bulk-action-selector').val();
	
	var data = {
		action: action,
		nonce: jQuery("input[name=nonce]").val(),
		directive: directive_val, 
		post_ids: selPostIds
	
	};	
	


	var dir_action_set = 'add';
	var to_from = 'to';
	if (directive_val.indexOf("_unset") >= 0){
		dir_action_set = 'remove';
		to_from = 'from';
	}

	var dir_action = 'NoIndex';
	if (directive_val.indexOf("nofollow") >= 0){
		dir_action = 'NoFollow';
	}


	var confirmation = confirm("Are you sure you want to "+dir_action_set+" the "+dir_action+" directive "+to_from+" these "+itemTyp+"?");

	if(confirmation){
		jQuery.post( ajaxurl, data, function(response) {
			
			// convert response from the AJAX request to JSON.
			var resp = jQuery.parseJSON(response)
				
			var status = resp.status;
			var directive = resp.directive;
			var post_ids = resp.post_ids;		
			var keyval = resp.val;

			if(status == 'OK'){
							
				//set the status of the checkboxes to update (i.e. 'checked' or 'unchecked'
				var checked_typ = null;
				if(keyval == 1){
					checked_typ = 'checked';
				}
							
				//iterate through the posts that need to have their checkboxes updated
				jQuery.each( post_ids, function( k, v ) {

			       jQuery("input[value="+v+"]."+directive+"-check").attr("checked", checked_typ);
			       
			    });

			}

			//let the user know that the posts are updated
			var upd_class = 'updated';
		    if(resp.status != 'OK'){
		    	upd_class = 'error';
		    }

		    var notification_response = '<div class="'+upd_class+' notice"><p>'+resp.msg+'</p></div>';
		    jQuery('.request-notification').html(notification_response);
		});
	}
  event.preventDefault();
});