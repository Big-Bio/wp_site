jQuery(document).ready(function($){

	function next1(){
		document.getElementById('first').style.display = 'none';
		document.getElementById('second').style.display = 'inline';
		// document.getElementById('third').style.display = 'none';
	}
	function next2(){
		document.getElementById('registration-header').style.display = "none";
		document.getElementById('first').style.display = 'none';
		document.getElementById('second').style.display = 'none';
		// document.getElementById('third').style.display = 'inline';
	}
	function prev2(){
		document.getElementById('first').style.display = 'inline';
		document.getElementById('second').style.display = 'none';
		// document.getElementById('third').style.display = 'none';
	}
	function removeMsg(){
		$(".status_error").empty();
	}
	function getCheckboxes(classname){
		var arr = [];
		var list = document.getElementsByClassName(classname);
		for (let item of list){
			if(item.checked){
				arr.push(item.value);
			}
		}
		return arr;
	}

	$('button#prev-2').on('click', function(e){
		prev2();
	});


	//loads json and inserts all countries, universities
	function load_json(){
		$.ajax({
		    type: 'POST',
		    dataType: 'json',
		    url: ajax_json_object.ajaxurl,
		    data: { 
		        'action': 'ajaxjson'
		     },

		    success: function(data){
		    	var json = JSON.parse(data);
		        var countries = json[0];
		        var states = json[1];
		        //load countries into select country
		        for(var i =0; i<countries.length; i++){
		        	var name = countries[i]['name'];
		        	var e = document.createElement('OPTION');
		        	e.textContent = name;
		        	e.value = name;
		        	if(name == 'United States'){
		        		e.selected = 'selected';
		        	}
		        	$('#country').append(e);
		        }
		        //load states into select state
		        for(var key in states){
		        	var e = document.createElement('OPTION');
		        	e.textContent = key;
		        	e.value = key;
		        	$('#state').append(e);
		        }

		    
		    }
		});
	}

	load_json();

	//if country is US, show state box. else, hide it
	var state_box = document.getElementById('state-box');
	document.getElementById('country').addEventListener('change', function(e){
		if(this.value == 'United States'){
			state_box.parentNode.style.display = 'inline';
		}
		else{
			state_box.parentNode.style.display = 'none';
		}
	});
	
	//calls ajax and verifies user account data
	$('div#first button#next-1').on('click', function(e){
	    $.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: ajax_register_object.ajaxurl,
	        data: { 
	            'action': 'ajaxregister1',
	            'security': $('#security').val(),
	            'fname' : $('#fname').val(),
	            'lname' : $('#lname').val(),
	            'username' : $('#username').val(),
	            'password1' : $('#pwd').val(),
	            'password2' : $('#pwd2').val(),
	             },

	        // beforeSend: function(){
	        // 	$('#next-1').css({'opacity':'0.5', 'background-color':
	        // 		'grey'});
	        // },
	        // complete: function(){
	        // 	$('#next-1').css({'opacity':'1', 'background-color': '#61B4E5'});
	        // },
	        success: function(data){
	            console.log(data);
	            removeMsg();

	            $.each(data.message, function(index, value){
	                var li = document.createElement('li');
	                li.textContent = value;
	                li.classList.add('error');
	                $('.status_error').append(li);
	            });
	            if(data.status){
	            	next1();
	            }
	        }
	    });
	    e.preventDefault();
	});

	$('div#second button#next-2').on('click', function(e){
		var ethnicity = getCheckboxes('ethnicity');
		var reason = getCheckboxes('reason');
		const vkey = new URLSearchParams(window.location.search).get('vkey');

		var state = $('#state').val();
		if($('#country').val() != 'United States'){
			state = '';
		}
		
	    $.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: ajax_register_object.ajaxurl,
	        data: { 
	            'action': 'ajaxregister2',
	            'security': $('#security').val(),
	            'fname' : $('#fname').val(),
	            'lname' : $('#lname').val(),
	            'username' : $('#username').val(),
	            'password1' : $('#pwd').val(),
	            'password2' : $('#pwd2').val(),
	            'age': $('#age').val(),
	            'gender': $('#gender').val(),
	            'country': $('#country').val(),
	            'state': state,
	            'degree': $('#degree').val(),
	            'years_schooling': $('#years_schooling').val(),
	            'rank_bio': $('#rank_bio').val(),
	            'rank_stats': $('#rank_stats').val(),
	            'rank_cs': $('#rank_cs').val(),
	            'occupation': $('#occupation').val(),
	            'employer': $('#employer').val(),
	            'primary_field': $('#primary_field').val(),
	            'ethnicity': ethnicity,
	            'reason': reason,
	            'vkey': vkey,
	             },

	        success: function(data){
	            console.log(data);
	            removeMsg();

	            $.each(data.message, function(index, value){
	                var li = document.createElement('li');
	                li.textContent = value;
	                li.classList.add('error');
	                $('.status_error').append(li);
	            });

	            if(data.status){
	            	window.location.href = ajax_register_object.redirecturl;
	            }
	            
	        }
	    });
	    e.preventDefault();
	});
});