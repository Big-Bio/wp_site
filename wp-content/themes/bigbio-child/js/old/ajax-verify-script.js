
function next1(){
	document.getElementById('first').style.display = "none";
	document.getElementById('second').style.display = "inline";
	document.getElementById('third').style.display = "none";
}
function next2(){
	var info = getInfo();
	ajax_verify_two_js(info);

    document.getElementById('registration-header').style.display = "none";
	document.getElementById('first').style.display = "none";
	document.getElementById('second').style.display = "none";
    document.getElementById('third').style.display = "inline";
}
function prev2(){


	document.getElementById('first').style.display = "inline";
	document.getElementById('second').style.display = "none";
	document.getElementById('third').style.display = "none";
}

function ajax_verify_one_js(){
    //gather module data
    const form = {
        messages: document.getElementsByClassName('error')[0],
        vkey: document.getElementById('vkey'),
        fname: document.getElementById('fname'),
        lname: document.getElementById('lname'),
        username: document.getElementById('username'),
        password1: document.getElementById('pwd'),
        password2: document.getElementById('pwd2'),
        submit: document.getElementById('submit'),
        security: document.getElementById('security'),
    };

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
            	while(form.messages.firstChild){
                    form.messages.removeChild(form.messages.firstChild);
                }
                next1();
            }
        }
    }
    var formData = new FormData();

    formData.append('action', 'ajax_verify_one');
    formData.append('security', form.security.value);
    formData.append('username', form.username.value);
    formData.append('password1', form.password1.value);
    formData.append('password2', form.password2.value);
    formData.append('fname', form.fname.value);
    formData.append('lname', form.lname.value);

    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
        
}

function ajax_verify_two_js(info){
    const form = {
        messages: document.getElementById('message'),
        security: document.getElementById('security'),
    };

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
                console.log(responseObject);
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
            	
            }
        }
    }
    var formData = new FormData();

    keys = Object.keys(info);

    for(var i =0; i<keys.length; i++){
    	formData.append(keys[i], info[keys[i]]);
    }

    formData.append('action', 'ajax_verify_two');
    formData.append('security', form.security.value);

   
    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
        
}

function getInfo(){
	var fname = document.getElementById('fname').value;
	var lname = document.getElementById('lname').value;
	var username = document.getElementById('username').value;
	var password = document.getElementById('pwd').value;

	var gbox = document.getElementById('gender')
	var gender = gbox.options[gbox.selectedIndex].value;
	
	var ethnicity = [];
	var ethbox = document.getElementsByName('ethnicity[]');
	for(var i =0; i<ethbox.length;i++){
		if(ethbox[i].checked == true){
			ethnicity.push(ethbox[i].value);
		}
	}
	var ethn_other = document.getElementById('eth-other').value;
	if(ethn_other){
		ethnicity.push(ethn_other);
	}

	var school = []
	var schoolbox = document.getElementsByName('school[]');
	for(var i =0; i<schoolbox.length;i++){
		if(schoolbox[i].checked == true){
			school.push(schoolbox[i].value);
		}
	}
	var school_other = document.getElementById('school-other').value;
	if(school_other){
		school.push(school_other);
	}

	var reason = []
	var reasonbox = document.getElementsByName('reason[]');
	for(var i =0; i<reasonbox.length; i++){
		if(reasonbox[i].checked == true){
			reason.push(reasonbox[i].value);
		}
	}
	var reason_other = document.getElementById('reason-other').value;
	if(reason_other){
		reason.push(reason_other);
	}

	var edu = []
	var edubox = document.getElementsByName('edu[]');
	for(var i =0; i<edubox.length; i++){
		if(edubox[i].checked == true){
			edu.push(edubox[i].value);
		}
	}
	var edu_other = document.getElementById('edu-other').value;
	if(edu_other){
		edu.push(edu_other);
	}

	var vkey = document.getElementById('vkey').value;

	var x = {'vkey': vkey, 'fname': fname, 'lname': lname, 'username': username, 'password': password, 'gender': gender, 'ethnicity': ethnicity, 'school': school, 'reason': reason, 'edu': edu};
	return x;
}