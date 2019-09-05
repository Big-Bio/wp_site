function ajax_module_js(){
    //gather module data
    const form = {
        title: document.getElementById('title'),
        content: document.getElementById('content'),

        messages: document.getElementById('status'),
        submit: document.getElementById('submit'),
        security: document.getElementById('security'),
        sup_notes: document.getElementById('sup-notes'),
        acknowledge: document.getElementById('acknowledge')

    };

    var collab = getTags('collab-cont');
    var doi = getTags('doi-cont');
    var keys = getTags('key-cont');

    var has_commas = false;

    for(let item of keys){
        if(item.includes(',')){
            has_commas = true;
        }
    }
    for(let item of doi){
        if(item.includes(',')){
            has_commas = true;
        }
    }
    for(let item of collab){
        if(item.includes(',')){
            has_commas = true;;
        }
    }

    const request = new XMLHttpRequest();

    //handles request response from function ajax_module
    request.onload = () => {
        let responseObject = null;

        try{
            responseObject = JSON.parse(request.responseText);
        } catch (e){
            console.error('Could not parse JSON!');
        }
        if(responseObject){
            console.log(responseObject);
            //if submission was not valid, send out error messages
            if(!responseObject.status){
                while(form.messages.firstChild){
                    form.messages.removeChild(form.messages.firstChild);
                }
                responseObject.messages.forEach((message) => {
                    const li = document.createElement('li');
                    li.textContent = message;
                    form.messages.appendChild(li);
                });
            }
            //otherwise disable button to prevent multiple clicks and redirect to dashboard
            else{
                // document.getElementById("submit").disabled = true;
                // location.replace("http://big-bio.org/beta/dashboard");
            }
        }
    }

    //insert gathered data in FormData and send a request to admin_ajax.php
    var formData = new FormData();

    formData.append('action', 'ajax_module');
    formData.append('title', form.title.value);
    formData.append('content', form.content.value);
    formData.append('pdf', form.pdf.files[0]);
    formData.append('ppt', form.ppt.files[0]);
    formData.append('tags', keys);
    formData.append('doi', doi);
    formData.append('collab', collab);
    formData.append('security', form.security.value);
    formData.append('sup_notes', form.sup_notes.value);
    formData.append('acknowledge', form.acknowledge.value);
    formData.append('invalid-keys', has_commas);

    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
        
}

function ajax_edit_module_js(val){
    //gather module data
    const form = {
        title: document.getElementById('title'),
        content: document.getElementById('content'),
        quiz: document.getElementById('quiz'),
        pdf: document.getElementById('pdf'),
        ppt: document.getElementById('ppt'),
        tags: document.getElementById('tags'),
        messages: document.getElementById('status'),
        submit: document.getElementById('submit'),
        security: document.getElementById('security'),
    };

    const request = new XMLHttpRequest();

    //handles request response from function ajax_module
    request.onload = () => {
        let responseObject = null;

        try{
            responseObject = JSON.parse(request.responseText);
        } catch (e){
            console.error('Could not parse JSON!');
        }
        if(responseObject){
            console.log(responseObject);
            //if submission was not valid, send out error messages
            if(!responseObject.status){
                while(form.messages.firstChild){
                    form.messages.removeChild(form.messages.firstChild);
                }
                responseObject.messages.forEach((message) => {
                    const li = document.createElement('li');
                    li.textContent = message;
                    form.messages.appendChild(li);
                });
            }
            //otherwise disable button to prevent multiple clicks and redirect to dashboard
            else{
                document.getElementById("submit").disabled = true;
                location.replace("http://big-bio.org/beta/dashboard");
            }
        }
    }

    //insert gathered data in FormData and send a request to admin_ajax.php
    var formData = new FormData();


    formData.append('action', 'ajax_edit_module');
    formData.append('id', val);
    formData.append('title', form.title.value);
    formData.append('content', form.content.value);
    formData.append('tags', form.tags.value);
    formData.append('pdf', form.pdf.files[0]);
    formData.append('ppt', form.ppt.files[0]);
    formData.append('security', form.security.value);

    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
        
}


function getTags(container){
    var box = document.getElementById(container);
    var div_children = box.children[0].children;

    var tags = [];

    for(let item of div_children){
        tags.push(item.textContent);
    }
    return tags;
}

// getting multiple files
function getFiles( ){ 
    

}

