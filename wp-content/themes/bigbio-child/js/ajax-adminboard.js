//last module that was pulled
var last_loaded_date;
//very last module
var last_module_date;

jQuery(document).ready(function($){

	var index = ['author_name', 'title', 'status', 'date_modified'];
	var app_index = ['title'];
	function inserDataRow(info, table){
	   	// var table = document.getElementById('module_table');

			for(var i = 0; i<info.length; i++){
				(function(){
					var row = table.insertRow(-1);
					row.id = "row" + i;
					for(var j = 0; j<4; j++){
						var temp = row.insertCell(j);
						if(index[j] == 'status'){
							temp.textContent = 'Pending';
							temp.style.color = '#00529B';
						}
						else{
							temp.textContent = info[i][index[j]];
						}
					}
					var a = row.insertCell(4);
					a.innerHTML = '<button class="preview" id="' + info[i]['module_id'] + '">Preview</Button>';
					var id = info[i]['module_id'];
					a.children[0].addEventListener('click', () => {
						preview_module(id);
					}, false);
					var b = row.insertCell(5);
					b.innerHTML = '<button>Revise</Button>';
					var c = row.insertCell(6);
					c.innerHTML = '<button class="module_approve" id="' + info[i]['module_id'] + '">Approve</Button>';
					var id = info[i]['module_id'];
					c.children[0].addEventListener('click', ()=> {
						approve_module(id);
					});
				}());
			}
	}

	function insertAppRow(info, table){
		console.log(info);
		for(var i = 0; i< info.length; i++){
			(function(){
				var row = table.insertRow(-1);
				row.id = "row" + i;

				var a = row.insertCell(0);
				a.textContent = info[i]['first_name'] + ' ' +  info[i]['last_name'];

				var b = row.insertCell(1);
				b.textContent = info[i]['highest_degree_earned'];

				var c = row.insertCell(2);
				c.innerHTML = '<a href="' + info[i]['cv_url'] + '" target="_blank">Link</a>';

				var d = row.insertCell(3);
				d.innerHTML = '<button class="app_approve" id="' + info[i]['user_ID'] + '">Approve</button>';
				var id = info[i]['user_ID'];
				d.children[0].addEventListener('click', ()=>{
					approve_user(id);
				});
			}());
		}
	}

	function preview_module(id){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_module_object.ajaxurl,
			data: { 
			    'action': 'ajaxgetdata',
			    'module_id': id
			 },

			success: function(data){
				var fields = data.message.data[0];
				var fields_cont = document.getElementsByClassName('modal-content')[0].children;
				var keys = Object.keys(fields);
				for(let key of keys){
					if(key == 'worksheet' || key == 'presentation'){
						document.getElementById(key).innerHTML = '<iframe src="https://docs.google.com/viewer?url=' + fields[key] + '&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe>';
					}	
					else{
						document.getElementById(key).innerHTML = fields[key];
					}
				}


				$('div.bg_modal').show();
			}
		});
	}

	$('div.close_but').on('click', (e) => {
		$('.bg_modal').hide();
	});

	var prev = document.getElementsByClassName('preview');

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: ajax_module_object.ajaxurl,
	    data: { 
	        'action': 'ajaxpreloadadmin',
	     },

	    success: function(data){
	    	var pending_data = data.message[0].data;
	    	var app_data = data.message[1].data;
	    	inserDataRow(pending_data, document.getElementById('module_table'));
	    	insertAppRow(app_data, document.getElementById('app_table'));
	    }
	});

	function approve_user(id){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_module_object.ajaxurl,
			data: { 
			    'action': 'ajaxappapprove',
			    'app_id': id,
			 },

			success: function(data){
				console.log(data);
				if(data.status){
					location.reload();
				}
			}
		});
	}

	function approve_module(id){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_module_object.ajaxurl,
			data: { 
			    'action': 'ajaxmoduleapprove',
			    'app_id': id,
			 },

			success: function(data){
				console.log(data);
				if(data.status){
					location.reload();
				}
			}
		});
	}
});