jQuery(document).ready(function($) {
    $('div.creator_content button#submit').on('click', function(e){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_test_object.ajaxurl,
            data: { 
                'action': 'ajaxtest',
                'title': $('#title').val(),
            },
            success: function(data){
                if (data.status){
                    console.log('yes');
                    console.log(data);
                }
                else{
                    console.log('no');
                    
                }
            }
        });
        e.preventDefault();
    });

});


// formData.append('action', 'ajax_module');
// formData.append('title', form.title.value);
// formData.append('content', form.content.value);
// formData.append('pdf', form.pdf);
// formData.append('ppt', form.ppt);
// formData.append('tags', keys);
// formData.append('doi', doi);
// formData.append('collab', collab);
// formData.append('security', form.security.value);
// formData.append('sup_notes', form.sup_notes.value);
// formData.append('acknowledge', form.acknowledge.value);
// formData.append('invalid-keys', has_commas);