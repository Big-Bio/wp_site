jQuery(document).ready(function($){
	//globals
	var last_date = '';
	var last_module_date = '';

	//insert new row into module table
	function insert_module_row(title, status, date_modified, id){
		var table = document.getElementById('module_table');
		var row = table.insertRow(-1);

		var cells = [];
		for(var i = 0; i < 5; i++){
			var c = row.insertCell(i);
			cells.push(c);
		}

		cells[0].textContent = title;
		if(status == 'pending'){
			cells[1].textContent = 'Pending';
			cells[1].style.color = '#00529B';
			cells[3].innerHTML = '<a href="../module/edit/?id=' + id + '"><button>Edit</button></a>';
			cells[4].innerHTML = '<button>Delete</button>';
		}
		else if(status == 'approved'){
			cells[1].textContent = 'Approved';
			cells[1].style.color = 'green';
			cells[0].innerHTML = '<a href="../module/?id=' + id + '">' + title + '</a>';
		}
		else if(status == 'revision'){
			cells[1].textContent = 'Revision Required';
			cells[1].style.color = '#9F6000';
			cells[3].innerHTML = '<a href="../module/edit/?id=' + id + '"><button>Edit</button></a>';
			cells[4].innerHTML = '<button>Delete</button>';
		}
		
		cells[2].textContent = date_modified;
		
	}

	//ajax call to get more modules to load into table
	function load_more_modules(){
		$.ajax({
		    type: 'POST',
		    dataType: 'json',
		    url: ajax_module_object.ajaxurl,
		    data: { 
		        'action': 'ajaxloadmore',
		        'last_date': last_date
		     },

		    success: function(data){
		    	console.log(data);
		    	var rows = data.rows;
	    	
		    	for(var i = 0; i < rows.length; i++){
		    		insert_module_row(rows[i].title, rows[i].status, rows[i].date_modified, rows[i].module_id);
		    		if(i == rows.length - 1){
			    		last_date = rows[i].date_modified;
			    		console.log(last_date);
			    		if(last_date == last_module_date){
			    			$('button#load_more').hide();
			    		}
			    	}
		    	}
		    }
		});
	}

	//ajax call to preload most recent modules into table
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: ajax_module_object.ajaxurl,
	    data: { 
	        'action': 'ajaxpreloaddash',
	     },

	    success: function(data){
	    	last_module_date = data.last_date;
	    	console.log(last_module_date);
	    	var rows = data.rows;
	    	for(var i = 0; i < rows.length; i++){
	    		insert_module_row(rows[i].title, rows[i].status, rows[i].date_modified, rows[i].module_id);
	    		if(i == rows.length - 1){
		    		last_date = rows[i].date_modified;
		    		console.log(last_date);
		    	}
	    	}
	    	}
	});

	document.querySelector('.close').addEventListener('click', function(){
		document.querySelector('.bg_modal').style.display = 'none';
	});

	document.querySelector('.delete_close').addEventListener('click', function(){
		document.querySelector('.bg-delete').style.display = 'none';
	});

	document.getElementById('load_more').addEventListener('click', function(){
		this.disabled = true;
		load_more_modules();
		//prevent multiple button presses
		setTimeout(function(e){e.disabled = false;}, 500, this);
	});
});

