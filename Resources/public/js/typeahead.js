$(document).ready(function(){
    
    $('.ajax-typeahead').typeahead({
        source: function(query, process) {
            return $.ajax({
                url: $(this)[0].$element[0].dataset.link,
                type: 'get',
                data: {
                    query: query
                },
                dataType: 'json',
                accepts: {
                    json: 'application/json; request=twitter.typeahead'
                },
                success: function(json) {
                    typeof json.options == 'undefined' ? false : process(json.options);
                    $('.selector-ttl').show();
                    $('.selector-found').html(json.ttl+' resultaten');
                    return;
                }
            });
        },
        minLength: 3,
        items: 10 /* TODO how to get this from config.yml? */
    });
    
});