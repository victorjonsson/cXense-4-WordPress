<?php
$has_paygate_plugin = defined('PAYGATE_PLUGIN_URL');
$type = 'other';
if( is_single() ) {
    $type = 'article';
} elseif( is_404() ) {
    $type = '404';
} elseif( is_singular() ) {
    $type = 'page';
} elseif( is_search() ) {
    $type = 'search';
}
?>
<div id="cX-root" style="display:none"></div>
<script type="text/javascript">

    var cX = cX || {};

    window.cXCustomParams = {
        type: '<?php echo $type ?>',
        paywall : '<?php echo $has_paygate_plugin && vkwp_is_post_closed() ? 'true':'false' ?>'
    };
    window.cXUserParams = {};
    window.cXenseSiteID = '<?php echo defined('CXENSE_DEV_SITE_ID') && CXENSE_DEV_SITE_ID ? CXENSE_DEV_SITE_ID:CXENSE_SITE_ID ?>';

    cX.callQueue = cX.callQueue || [];
    cX.callQueue.push(['setSiteId', cXenseSiteID]);
    cX.callQueue.push(['setCustomParameters', cXCustomParams]);
    cX.callQueue.push(['setUserProfileParameters', cXUserParams]);
    cX.callQueue.push(['sendPageViewEvent', { useAutorefreshCheck: false <?php if(defined('CXENSE_REPORT_LOCATION')): ?>, location :'<?php echo CXENSE_REPORT_LOCATION ?>' <?php endif; ?>}]);

    var cXenseInit = function() {

        var checkIfNotAccessedByIP = <?php echo $has_paygate_plugin && is_behind_paygate() ? 'true':'false' ?>;

        if( !checkIfNotAccessedByIP || (window.PayGateUser && window.PayGateUser.hasWebAccess()) ) {
            window.cXCustomParams['subscriber'] = window.PayGateUser && window.PayGateUser.hasWebAccess() ? 'true':'false';

            if( window.cXCustomParams['subscriber'] == 'true' ) {
                window.cXUserParams['subscriber'] = 'true';
                for(var i=0; i<window.PayGateUser.products.length; i++ ) {
                    var prod = window.PayGateUser.products[i];
                    if( prod.indexOf('_digital') > -1 || prod.indexOf('_premium') > -1 || prod.indexOf('_bas') > -1 ) {
                        window.cXUserParams.product = prod;
                        break;
                    }
                }
            } else {
                window.cXUserParams['subscriber'] = 'false';
            }

            (function() { try { var scriptEl = document.createElement('script'); scriptEl.type = 'text/javascript'; scriptEl.async = 'async';
                scriptEl.src = ('https:' == document.location.protocol) ? 'https://scdn.cxense.com/cx.js' : 'http://cdn.cxense.com/cx.js';
                var targetEl = document.getElementsByTagName('script')[0]; targetEl.parentNode.insertBefore(scriptEl, targetEl); } catch (e) {};} ());
        }
    };

    if( 'jQuery' in window && 'PayGateConfig' in window ) {
        jQuery(window).bind('paygateLoaded', cXenseInit);
    } else {
        cXenseInit();
    }

    // Page swipe event (can be used to trigger page view via AJAX)
    if( 'jQuery' in window ) {
        jQuery(window).bind('pageSwipe', function() {
            window.cXCustomParams['swipe'] = 'true';
            cX.initializePage();
            cX.setSiteId(window.cXenseSiteID);
            cX.setCustomParameters(window.cXCustomParams);
            cX.setUserProfileParameters(window.cXUserParams);
            cX.sendPageViewEvent();
        });
    }

</script>
