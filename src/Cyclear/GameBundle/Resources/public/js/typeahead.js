$(document).ready(function () {


    var defaultRiderGet = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: '/renners/get?query=%QUERY',
            wildcard: '%QUERY'
        }
    });

    $('.ajax-typeahead').typeahead(null, {
        name: 'default-rider-get',
        display: 'value',
        limit: Infinity,
        highlight: true,
        hint: true,
        source: defaultRiderGet,
        placeholder: 'Renner...',
        templates: {
            empty: [
                '<div class="empty-message">',
                'No riders matching this criterium',
                '</div>'
            ].join('\n'),
            suggestion: function (data) {
                return '<div>[' + data.identifier + '] <strong>' + data.name + '</strong></div>';
            }
        }
    }).bind('typeahead:selected', function (event, obj) {
        if (event.currentTarget.hasAttribute('do-redirect')) {
            event.preventDefault();
            window.location = Routing.generate('renner_show', {seizoen: seizoenSlug, renner: obj.slug});
        }
    });
});