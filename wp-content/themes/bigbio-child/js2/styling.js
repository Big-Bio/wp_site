// js for styling / user friendliness

// create module 

function add_file(e,_this){ 
	var id = _this.id;
	var container = _this.previousElementSibling;

	var fileName = '';
	if( _this.files && _this.files.length > 1) fileName = _this.files.length + ' files selected';
	else fileName = _this.value.substring(_this.value.lastIndexOf('\\')+1);;

	var temp = container.querySelector('span');
	temp.innerHTML = fileName;
}

