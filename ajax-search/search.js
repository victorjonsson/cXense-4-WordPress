function toggleAdvanced(){
    jQuery("#advanced-search-form").toggle();
}
function cxenseSearch(pagination){
    var sort = jQuery('input[name=sort]:checked').val();
    var searchTerm =  jQuery("#search-term").val();
    var count = jQuery("#search-count").val();
    var selected = new Array();
    var page = pagination
    jQuery('#search-options input:checked').each(function() {
        selected.push(jQuery(this).attr('name'));
    });
    alert(page);
    jQuery.post( cxense_data.post_url,{
        sort: sort,
        selected:selected.toString(),
        searchTerm:searchTerm,
        count: count,
        pagination: page

    }, function( data ) {
        jQuery( "#search-result" ).html( data );
    });
}

jQuery(document).keypress(function(e) {
    if(e.which == 13) {
        cxenseSearch();
    }
});