
var all_tag_divs = ['collab-cont', 'doi-cont', 'key-cont'];
var all_tag_placeholders = { 
    'collab-cont': "Jane Goddall", 
    'doi-cont': "10.1172/JCI25421",
    'key-cont': "New Key Term"
};
var files = { 
    ppt: [], 
    pdf: []
};

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

function add_file(e,_this){ 
    // if no file selected don't do anything
    // ppt and pdf files must be stored separately (localstorage?)
    var id = _this.id;
    var container = _this.previousElementSibling;
    var input = _this.value;

    var i = files[id].length;

    files[id].push(_this.files[0]);

    var temp = document.createElement('DIV');
    temp.setAttribute('data', "index: '-1'");
    temp.dataset.index = i;
    var close = document.createElement('SPAN');
    close.classList.add('close');
    temp.classList.add('tag');

    temp.innerHTML = input.substring(input.lastIndexOf('\\')+1);
    close.addEventListener('click', function(e){
        delete files[id][temp.dataset.index];
        temp.remove();
        files[id] = files[id].filter(Boolean);
        console.log(files)
    });

    container.appendChild(temp);
    container.children[container.children.length - 1].appendChild(close);

}