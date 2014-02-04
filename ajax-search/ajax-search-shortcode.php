<?php


function cxense_output_ajax_search() {
    wp_enqueue_script('cxense-ajax-search', CXENSE_PLUGIN_URL.'/ajax-search/search.js', array('jquery'), CXENSE_PLUGIN_VERSION);
    $data = array('post_url' => plugin_dir_url(__FILE__).'ajax/cxense-api-search.php');
    wp_localize_script('cxense-search', 'cxense_data', $data);
    ?>
    <div id="search-wrapper">
        <input type="text" placeholder="Sök..." name="search_term" id="search-term">
        <input type="button" class="button-primary" onclick="cxenseSearch();" value="Sök">
        <input type="button" class="button-secondary" onclick="toggleAdvanced();" value="Inställningar">
    </div>
    <div  id="advanced-search-form" style="display: none" type="text">
        <div id="search-options">
            <span>Sök på:</span>
            <input type="checkbox" checked="checked" name="author"> Författare |
            <input type="checkbox" checked="checked" name="title"> Titel |
            <input type="checkbox" checked="checked" name="description"> Beskrivning |
            <input type="checkbox" checked="checked" name="body"> Innehåll
        </div>
        <div>
            <span>Sortera efter:</span>
            <input type="radio" name="sort" value="date" checked="checked">Datum |
            <input type="radio" name="sort" value="relevance"> Relevans
        </div>
        <div>
            <span>Antal sökträffar:</span>
            <select id="search-count">
                <option>10</option>
                <option>20</option>
                <option selected="selected">30</option>
                <option>40</option>
                <option>50</option>
                <option>60</option>
                <option>70</option>
                <option>80</option>
                <option>90</option>
                <option>100</option>
            </select>
        </div>
    </div>
    <div id="search-result"></div>
<?

}
add_shortcode('cxense-search', 'cxense_output_ajax_search');