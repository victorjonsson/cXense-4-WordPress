<?php

function cxense_output_ajax_search() {
    wp_enqueue_script('cxense-ajax-search', CXENSE_PLUGIN_URL.'ajax-search/search.js', array('jquery'), CXENSE_PLUGIN_VERSION);
    $data = array('post_url' => plugin_dir_url(__FILE__).'api.php');
    wp_localize_script('cxense-ajax-search', 'cxense_data', $data);
    $value = isset($_GET['q']) ? $_GET['q'] : '';
    if($value != ''){
        ?> <script type="application/javascript"> jQuery(document).ready(function(){
                cxenseSearch();
            })</script>
        <?php
    }
    ?>
    <div class="search-wrapper">
        <input type="text" placeholder="Sök..." name="search_term" class="search-input" id="search-term" value="<?=$value?>">
        <input type="button" class="btn btn--dark" onclick="cxenseSearch();" value="Sök">
        <input type="button" class="btn btn-settings" value="Inställningar">

        <div class="search-settings js-search-settings">
            <div id="search-options">
                <span class="option-title">Sök på:</span>
                <label class="option"><input type="checkbox" checked="checked" name="author"> Författare</label>
                <label class="option"><input type="checkbox" checked="checked" name="title"> Titel</label>
                <label class="option"><input type="checkbox" checked="checked" name="description"> Beskrivning</label>
                <label class="option"><input type="checkbox" checked="checked" name="body"> Innehåll</label>
            </div>
            <div>
                <span class="option-title">Sortera efter:</span>
                <label class="option"><input type="radio" name="sort" value="date" checked="checked">Datum </label>
                <label class="option"><input type="radio" name="sort" value="relevance"> Relevans</label>
            </div>
            <div>
                <span class="option-title">Antal sökträffar:</span>
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
    </div>


    <div class="search-results js-search-results"></div>
<?php

}
add_shortcode('cxense-search', 'cxense_output_ajax_search');
?>
