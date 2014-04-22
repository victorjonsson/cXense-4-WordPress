<?php

if(isset($_POST['searchTerm']) && isset($_POST['selected']) && isset($_POST['sort']) && isset($_POST['count'])){
    require_once '../../../../wp-load.php';

    $display_image = false;
    $pagination = isset($_POST['pagination']) ? $_POST['pagination'] : 0;
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
        $returnpage = '<a class="prev" href="#" onclick="cxenseSearch('.($pagination -$_POST['count']).')">Föregående sida</a>';
    } else {
        $returnpage = '';
    }

    if($totalscope < $total_matched){
        $nextpage = '<a class="next" href="#" onclick="cxenseSearch('.$totalscope.')">Nästa sida</a>';
    } else {
        $nextpage = '';
    }

    $html = '<div class="search-results__info">'.$pagination.'- '.$totalscope.' av <strong>'.$total_matched.'</strong> sökträffar ( tips, använd inställningar för att förfina det du söker efter )</div>';

    $html .= '<div class="search__pager">'.$returnpage.$nextpage.'</div>';

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
        $html .= '<article class="search-results__item">
                    '.$show_image.'
                        <span class="date">'.$date.'</span>
                        <h2><a href="'.$href.'">'.$title.'</a></h2>
                        <p class="excerpt">'.$description.'</p>
                        <div class="body">'.$body.'</div>
                 </article>';
    }
    echo $html;

}