<?php
$url = rtrim(get_bloginfo('home'), '/');
$url .= isset($_GET['path']) ? '/'.ltrim(urldecode($_GET['path']), '/') : '/';
$encode_param = function($name) {
    if( isset($_GET[$name]) && is_array($_GET[$name]) ) {
        return json_encode($_GET[$name]);
    } else {
        return '{}';
    }
};
?><html>
<head>
    <title>cx-event: <?php echo $url ?></title>
</head>
<body>
    <script>

        var cX = {callQueue: []};
        cX.callQueue.push(['setSiteId', '<?php echo cxense_get_opt('cxense_site_id') ?>']);
        cX.callQueue.push(['setCustomParameters', <?php echo $encode_param('customParam'); ?>]);
        cX.callQueue.push(['setUserProfileParameters', <?php echo $encode_param('userParam');  ?>]);
        cX.callQueue.push(['sendPageViewEvent', { useAutorefreshCheck: false, location: '<?php echo $url ?>'}]);

        (function() { try { var scriptEl = document.createElement('script'); scriptEl.type = 'text/javascript'; scriptEl.async = 'async';
            scriptEl.src = ('https:' == document.location.protocol) ? 'https://scdn.cxense.com/cx.js' : 'http://cdn.cxense.com/cx.js';
            var targetEl = document.getElementsByTagName('script')[0]; targetEl.parentNode.insertBefore(scriptEl, targetEl); } catch (e) {};} ());

    </script>
</body>
</html>