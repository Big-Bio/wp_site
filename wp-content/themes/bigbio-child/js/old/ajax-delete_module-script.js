function preview_delete(post_id, num){
	document.querySelector('.bg-delete').style.display = 'flex';
	document.getElementById('module_id').value = post_id;
    document.getElementById('row_id').value = num;
}

function ajax_delete_js(){
    const form = {
        row: document.getElementById('row_id'),
    	id: document.getElementById('module_id'),
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
        }
        document.querySelector('.bg-delete').style.display = 'none';
        removeElement('row'+ form.row.value);
    }

    var formData = new FormData();

    formData.append('action', 'ajax_delete_module');
  	formData.append('id', form.id.value);
    formData.append('security', form.security.value);

    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
}

function preview_pop(title, content, ppt_url, pdf_url){
	document.querySelector('.bg_modal').style.display = 'flex';
	document.getElementById('title').innerHTML = title;
	document.getElementById('content').innerHTML = content;
	document.getElementById('pdf').innerHTML = '<iframe src="https://docs.google.com/viewer?url=' + pdf_url + '&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe>';
	document.getElementById('ppt').innerHTML = '<iframe src="https://docs.google.com/viewer?url=' + ppt_url + '&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe>';
}

function ajax_approve_js(input_id, row){
    const form = {
        security: document.getElementById('security'),
        check: document.getElementById('publish_check')
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
            if(responseObject.status){
                if(responseObject.post_status == 'true'){
                    var num = 'row' + row;
                    var status = document.getElementById(num).children[2];
                    status.style.color = 'green';
                    status.textContent = 'Published';

                    var name = document.getElementById(num).children[1];
                    var title = name.textContent;
                    name.innerHTML = '<a href="' + responseObject.url + '">' + title + '</a>';
                }
                else{
                    var num = 'row' + row;
                    var status = document.getElementById(num).children[2];
                    status.style.color = '#FF851B';
                    status.textContent = 'Pending';

                    var name = document.getElementById(num).children[1];
                    var title = name.textContent;
                    name.innerHTML = responseObject.title;
                }
            }
            
        }
    }

    var formData = new FormData();

    formData.append('action', 'ajax_publish_module');
    formData.append('id', input_id);
    formData.append('security', form.security.value);
    formData.append('check', form.check.checked);
    request.open('POST', '/beta/wp-admin/admin-ajax.php' , true);
    request.send(formData);
}

function removeElement(eleID){
    var element = document.getElementById(eleID);
    element.parentNode.removeChild(element);
}