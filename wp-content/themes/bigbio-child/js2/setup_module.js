jQuery(document).ready(function($){
	const module_id = new URLSearchParams(window.location.search).get('id');
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: ajax_module_object.ajaxurl,
	    data: { 
	        'action': 'ajaxdisplaymodule',
	        'module_id': module_id
	     },

	    success: function(data){
	    	if(data.status){
	    		mod_data = data.info;
	    		var keys = ['title', 'collab','doi','content','sup_notes','author_name','ack'];
	    		for(var i =0; i<keys.length; i++){
	    			var x = document.getElementById(keys[i]);
	    			x.innerHTML = mod_data[keys[i]];
	    		}
	    		
	    		console.log(mod_data);
	    	}
	    	else{
	    		
	    	}
	    }
	});
});