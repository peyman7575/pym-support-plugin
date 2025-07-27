jQuery(function($){
    $('#pym-new-ticket').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        $.post(pym_obj.ajax_url, form.serialize())
            .done(function(resp){
                alert(resp.data);
                if(resp.success){
                    location.reload();
                }
            });
    });
});
