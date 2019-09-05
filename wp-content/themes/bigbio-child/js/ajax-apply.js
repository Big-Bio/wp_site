file = [];

jQuery(document).ready(function($) {

	$('.tag').hide();
	$('input#cv').change(function() {
		file[0] = this.files[0];
		$('input#cv').hide();
		$('label[for="cv"]').hide();
		$('.tag').text(file[0]['name']);
		$('.tag').append('<div class="close" id="cls"></div>');
		$('.tag').show();

		$('div.close#cls').on('click', function(){
			$('.tag').hide();
			$('input#cv').show();
			$('label[for="cv"]').show();
			file[0] = null;
		});

		console.log(file[0]);
		setTimeout(function(){}, 300);
	});



	$('button#submit').on('click', function(){
		const request = new XMLHttpRequest();
		
		request.onload = () => {
		    let responseObject = null;
		    try{
		        responseObject = JSON.parse(request.responseText);
		    } catch (e){
		        console.error('Could not parse JSON!');
		    }
		    if(responseObject){
		        console.log(responseObject);
		        if(!responseObject.status){
		           $(".status_error").empty();

		           $.each(responseObject.message, function(index, value){
		               var li = document.createElement('li');
		               li.textContent = value;
		               li.classList.add('error');
		               $('.status_error').append(li);
		           });
		        }
		        else{
		        	$(".status_error").empty();
		            window.location.href = "http://big-bio.org/beta/dashboard";
		        }
		    }
		}

		var formData = new FormData();
		formData.append('action','ajaxapply');
		formData.append('degree', $('#degree').val());
		formData.append('cv', file[0]);
		formData.append('phone', $('#phone').val());
		request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
		request.send(formData);
	});
});