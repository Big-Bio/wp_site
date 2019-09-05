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
	    		document.getElementById('data').textContent = JSON.stringify(data);
	    	}
	    	else{
	    		document.getElementById('data').textContent = data.message;
	    	}
	    }
	});
});