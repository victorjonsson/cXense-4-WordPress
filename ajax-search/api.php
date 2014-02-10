<?php

if(isset($_POST['searchTerm']) && isset($_POST['selected']) && isset($_POST['sort']) && isset($_POST['count'])){
    require_once '../../../../wp-load.php';

    $display_image = false;
    $pagination = !empty($_POST['pagination']) ? 0 : $_POST['pagination'];
    $search_args = array(
        'columns' => $_POST['selected'],
        'count' => $_POST['count'],
        'pagination' => $pagination
    );
    $result = cxense_search($_POST['searchTerm'], $search_args);

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