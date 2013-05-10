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
                    var options = [];
                    for(i in json){
                        options.push(json[i].naam);
                    }
                    typeof json == 'undefined' ? false : process(options);
                    $('.selector-ttl').show();
                    $('.selector-found').html(options.length+' resultaten');
                    return;
                }
            });
        },
        minLength: 3,
        items: 10 /* TODO how to get this from config.yml? */
    });
    
});