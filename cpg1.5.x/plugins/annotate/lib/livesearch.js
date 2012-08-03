var loaded = false;

$(document).ready(function() {
    var alertTimerId = 0;
    $('#livesearch_input').keyup(function() {
        $('#livesearch_input').addClass('blue');
        clearTimeout(alertTimerId);
        alertTimerId = setTimeout(function () {
            $.post('index.php?file=annotate/reqserver', {livesearch:'1',q:$('#livesearch_input').val()}, function(data) { 
                $('#livesearch_output').html(data); 
                $('#livesearch_input').removeClass('blue');
            });
            loaded = true;
        }, 250);
    });
});

function load_annotation_list() {
    if (loaded == false) {
        $('#livesearch_output').attr('disabled', 'disabled');
        $('#livesearch_output_loading').show();
        $.post('index.php?file=annotate/reqserver', { livesearch: '1', q: $('#livesearch_input').val() }, function(data) {
            $('#livesearch_output_loading').hide();
            $('#livesearch_output').html(data).removeAttr('disabled'); 
        });
        loaded = true;
    }
}