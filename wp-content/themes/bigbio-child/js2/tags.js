var all_tag_divs = ['collab-cont', 'doi-cont', 'key-cont'];

var all_tag_placeholders = { 
	'collab-cont': "Jane Goddall", 
	'doi-cont': "10.1172/JCI25421",
	'key-cont': "New Key Term"
};

all_tag_divs.forEach(function(element){
	setup_input(document.getElementById(element), element);
});

function setup_input(container, type){
	var all_tags = document.createElement('div');
	all_tags.classList.add('tags');
	container.appendChild(all_tags);
	var input = document.createElement('input');
	input.type = "text";
	input.style = "margin-top: 15px;";
	input.placeholder = all_tag_placeholders[type];

	input.onkeypress = function(e){
		if(!e) e = window.event;
		var keyCode = e.keyCode || e.which;
		if(keyCode == '13'){
			if(input.value != ""){
				add_tag(all_tags, input.value);
				input.value = "";
			}
		}
	}
	container.appendChild(input);
}

function add_tag(container, input, type){
	var temp = document.createElement('DIV');
	var close = document.createElement('SPAN');
	close.classList.add('close');

	temp.classList.add('tag');

	temp.innerHTML = input;
	close.addEventListener('click', function(e){
		temp.remove();
	});
	container.appendChild(temp);
	container.children[container.children.length - 1].appendChild(close);
}

function doi_tag(container, input){
	let example = new Cite('Q21972834');

	let output = example.format('bibliography', {
	  format: 'html',
	  template: 'apa',
	  lang: 'en-US'
	})
	console.log(output);
}