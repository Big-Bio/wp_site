jQuery(document).ready(function($){
	$('button#submit').on('click', function(e){
	    $.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: ajax_sendemail_object.ajaxurl,
	        data: { 
	            'action': 'ajaxsendemail', //calls wp_ajax_nopriv_ajaxlogin
	            'email': $('#email').val(),
	            'security': $('#security').val() },
	        success: function(data){
	            console.log(data);
	            $("#status.status_error").empty();

	            $.each(data.message, function(index, value){
	                var li = document.createElement('li');
	                li.textContent = value;
	                li.classList.add('error');
	                $('#status.status_error').append(li);
	            });

	            if(data.status){
	            	document.getElementById('verify').style.display = 'inline';
	            	document.getElementById('email-form').style.display = 'none';
	            }
	            
	        }
	    });
	    e.preventDefault();
	});
});