
function ajax_email_js(){
    //gather module data
    const form = {
        email: document.getElementById('email'),
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
            else{
                document.getElementById('email-form').style.display = "none";
                document.getElementById('verify').style.display = "flex";
                form.messages.style.display = "none";
            }
        }
    }

    //insert gathered data in FormData and send a request to admin_ajax.php
    var formData = new FormData();

    formData.append('action', 'ajax_email_verify');
    formData.append('email', form.email.value);
    formData.append('security', form.security.value);

    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
        
}