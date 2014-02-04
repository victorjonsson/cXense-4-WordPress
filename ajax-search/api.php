<?php

if(isset($_POST['searchTerm']) && isset($_POST['selected']) && isset($_POST['sort']) && isset($_POST['count'])){
    require_once '../../../../wp-load.php';

    if($_POST['sort'] == 'date'){
        $sort = '&p_sm=timestamp:desc';
    } else {
        $sort = '';
    }
    $display_image = false;
    $pagination = (isset($_POST['pagination']) && $_POST['pagination'] != '') ? $_POST['pagination'] : 1;

    $url = 'http://sitesearch.cxense.com/api/search/'.cxense_get_opt('cxense_site_id').'?p_aq=query('. $_POST['selected'].':"'.urlencode($_POST['searchTerm']).'",token-op=and)&p_c='.$_POST['count'].$sort.'&p_s='.$pagination;

    if(get_transient(md5($url)) == false){
        $response = wp_remote_get($url);
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            error_log( "Something went wrong trying to get cxense search data: $error_message");
        } else {
            $result =  json_decode($response['body'], true);
            set_transient(md5($url),$result , 1 *HOUR_IN_SECONDS );
        }
    } else {
        $result = get_transient(md5($url));
    }

    $total_matched = isset($result['totalMatched']) ? $result['totalMatched'] : 0;

    if(($_POST['count']+$pagination) <= $total_matched ){
        $totalscope = $_POST['count']+$pagination;
    } else {
        $totalscope = $total_matched;
    }

    if($pagination > 1){
        $returnpage = '<a href="#" onclick="cxenseSearch('.($pagination -$_POST['count']).')">Föregående sida</a> |';
    } else {
        $returnpage = '';
    }

    if($totalscope < $total_matched){
        $nextpage = '<a href="#" onclick="cxenseSearch('.$totalscope.')">Nästa sida</a>';
    } else {
        $nextpage = '';
    }

    $html = '<div id="search-result">'.$pagination.'- '.$totalscope.' av <strong>'.$total_matched.'</strong> sökträffar ( tips, använd inställningar för att förfina det du söker efter )</div>';

    $html .= '<div>'.$returnpage.$nextpage.'</div>';

    foreach ($result['matches'] as $match){
        $href = isset($match['document']['fields']['link-canonical']) ? $match['document']['fields']['link-canonical'] : '';
        $title = isset($match['document']['fields']['title']) ? $match['document']['fields']['title'] : '';
        $description = isset($match['document']['fields']['description']) ? $match['document']['fields']['description'] : '';
        $body = isset($match['document']['fields']['body'][0]) ? $match['document']['fields']['body'][0] : '';
        $date = isset($match['document']['fields']['timestamp']) ? date('Y-m-d H:i',strtotime($match['document']['fields']['timestamp'])) : '';
        $image = isset($match['document']['fields']['thumbnail']) ? $match['document']['fields']['thumbnail'] : '';
        if($display_image){
            $show_image = '<div class="search-image"><img src="'.$image.'" alt="search-image"></div>';
        } else {
            $show_image = '';
        }
        $html .= '<div class="search-result" style="display:flex">
                    '.$show_image.'
                    <div class="search-content">
                        <table>
                            <tr>
                                <td><a href="'.$href.'">'.$title.'</a></td>
                            </tr>
                            <tr>
                                <td><span class="search-description">'.$description.'</span></td>
                            </tr>
                            <tr>
                                <td><span class="search-body">'.$body.'</span></td>
                            </tr>
                            <tr>
                                <td><span class="search-pubdate">'.$date.'</span></td>
                            </tr>
                        </table>
                    </div>
                 </div>';
    }
    echo $html;

}