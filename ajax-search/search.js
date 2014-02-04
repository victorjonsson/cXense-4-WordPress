function toggleAdvanced(){
    jQuery("#advanced-search-form").toggle();
}
function cxenseSearch(pagination){
    var sort = jQuery('input[name=sort]:checked').val();
    var searchTerm =  jQuery("#search-term").val();
    var count = jQuery("#search-count").val();
    var selected = new Array();
    jQuery('#search-options input:checked').each(function() {
        selected.push(jQuery(this).attr('name'));
    });
    jQuery.post( cxense_data.post_url,{
        sort: sort,
        selected:selected.toString(),
        searchTerm:searchTerm,
        count: count,
        pagination: pagination

    }, function( data ) {
        jQuery( "#search-result" ).html( data );
    });
}