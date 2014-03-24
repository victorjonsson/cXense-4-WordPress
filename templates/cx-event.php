<?php
$encode_param = function($name) {
    if( isset($_GET[$name]) && is_array($_GET[$name]) ) {
        return json_encode($_GET[$name]);
    } else {
        return '{}';
    }
};
$cx_event = 'Event: '. str_replace('/cxense-event/', '', strip_tags(current(explode('?', $_SERVER['REQUEST_URI']))));
?><html>
<head>
    <title><?php echo $cx_event ?></title>
    <?php cxense_output_meta_tags(null, 'cXense Event', $cx_event, $cx_event) ?>
</head>
<body>
    <script>
        var cX = {callQueue: []};
        cX.callQueue.push(['setSiteId', '<?php echo cxense_get_opt('cxense_site_id') ?>']);
        cX.callQueue.push(['setCustomParameters', <?php echo $encode_param('customParam'); ?>]);
        cX.callQueue.push(['setUserProfileParameters', <?php echo $encode_param('userParam');  ?>]);
        cX.callQueue.push(['sendPageViewEvent', { useAutorefreshCheck: false}]);

        (function() { try { var scriptEl = document.createElement('script'); scriptEl.type = 'text/javascript'; scriptEl.async = 'async';
            scriptEl.src = ('https:' == document.location.protocol) ? 'https://scdn.cxense.com/cx.js' : 'http://cdn.cxense.com/cx.js';
            var targetEl = document.getElementsByTagName('script')[0]; targetEl.parentNode.insertBefore(scriptEl, targetEl); } catch (e) {};} ());

    </script>
</body>
</html>