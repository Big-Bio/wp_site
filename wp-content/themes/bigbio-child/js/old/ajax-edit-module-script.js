
function ajax_edit_module_js(){
    //gather module data
    const form = {
        title: document.getElementById('title'),
        content: document.getElementById('content'),
        pdf: document.getElementById('pdf'),
        ppt: document.getElementById('ppt'),
        messages: document.getElementById('status'),
        submit: document.getElementById('submit'),
        security: document.getElementById('security'),
        sup_notes: document.getElementById('sup-notes'),
        acknowledge: document.getElementById('acknowledge'),
        id: document.getElementById('post_id'),
        yt: document.getElementById('yt'),
        quiz: document.getElementById('quiz')
    };

    var collab = getTags('collab-cont');
    var doi = getTags('doi-cont');
    var keys = getTags('key-cont');

    var has_commas = 'false';

    for(let item of keys){
        if(item.includes(',')){
            has_commas = 'true';
        }
    }
    for(let item of doi){
        if(item.includes(',')){
            has_commas = 'true';
        }
    }
    for(let item of collab){
        if(item.includes(',')){
            has_commas = 'true';
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
                document.getElementById("submit").disabled = true;
                console.log(location.pathname);
                if(location.pathname == '/beta/edit-module-admin'){
                    location.replace("http://big-bio.org/beta/admin");
                }
                else{
                    location.replace("http://big-bio.org/beta/dashboard");
                }
            }
        }
    }

    //insert gathered data in FormData and send a request to admin_ajax.php
    var formData = new FormData();

    formData.append('action', 'ajax_edit_module');
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
    formData.append('post_id', form.id.value);
    if(location.pathname == '/beta/edit-module-admin'){
        formData.append('quiz', form.quiz.value);
        formData.append('yt', form.yt.value);
    }
    

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