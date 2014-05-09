<?php

// Do we have the paygate plugin installed?
$has_paygate_plugin = defined('PAYGATE_PLUGIN_URL');


// Determine which type of request that is being made
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

// Get user products
$userProducts = array();
foreach(explode(',', cxense_get_opt('cxense_user_products')) as $prod) {
    $userProducts[] = trim($prod);
}
?>
<div id="cX-root" style="display:none"></div>
<script type="text/javascript">

    var cX = cX || {},
        cXCustomParams = cXCustomParams || {},
        cXUserParams = cXUserParams || {};

    cXCustomParams.type =  '<?php echo $type ?>';
    cXCustomParams.paywall = '<?php echo $has_paygate_plugin && is_paygate_protected() ? 'true':'false' ?>';
    cXCustomParams.webView = /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(navigator.userAgent) ? 'true':'false';
    cXenseSiteID = '<?php echo cxense_get_opt('cxense_site_id') ?>';
    cX.callQueue = cX.callQueue || [];


    var cXenseInit = function() {

        window.cXCustomParams['subscriber'] = window.PayGateUser && window.PayGateUser.hasWebAccess() ? 'true':'false';

        var isBehindPaygate = <?php echo $has_paygate_plugin && is_behind_paygate() ? 'true':'false' ?>,
            isSharedPaygate = isBehindPaygate && window.cXCustomParams['subscriber'] == 'false'

        if( window.userGender ) {
            window.cXCustomParams['gender'] = window.userGender;
        }
        if( window.userAgeGroup ) {
            window.cXCustomParams['ageGroup'] = window.userAgeGroup;
        }

        if( !isBehindPaygate || isSharedPaygate || window.cXCustomParams['subscriber'] == 'true' ) {

            if( window.cXCustomParams['subscriber'] == 'true' ) {
                window.cXUserParams['subscriber'] = 'true';
                <?php if( !empty($userProducts) ): ?>
                    var userProducts = <?php echo json_encode($userProducts) ?>;
                    jQuery.each(userProducts, function(i, prod) {
                        if( window.PayGateUser.hasProduct(prod) ) {
                            window.cXUserParams['product'] = prod;
                            return false;
                        }
                    });
                <?php endif; ?>

                <?php if( $org_type = cxense_get_opt('cxense_org_prefix') ): ?>
                    cX.callQueue.push(['addExternalId', {id: window.PayGateUser.id, type: '<?php echo $org_type ?>'}]);
                <?php endif; ?>

            } else {
                window.cXUserParams['subscriber'] = 'false';
            }

            <?php do_action('cxense_js_init') ?>

            jQuery(window).trigger('cXenseInit');

            /**
             * Method that can be used to trigger a cXense Page view event
             * @param [customParams]
             * @param [userProfileParams]
             * @param [path]
             */
            window.sendCxenseEvent = function(customParams, userProfileParams, path) {
                customParams = customParams || {};
                userProfileParams = userProfileParams || {};

                if( path ) {
                    // Load specified url
                    var iframeHref = '<?php echo trim(bloginfo('url'), '/'); ?>/cxense-event'+path+'?';
                    jQuery.each(customParams, function(key, val) {
                        iframeHref += 'customParam['+key+']='+val+'&amp;';
                        window.cXCustomParams[key] = val;
                    });
                    jQuery.each(userProfileParams, function(key, val) {
                        iframeHref += 'userParam['+key+']='+val+'&amp;';
                    });
                    jQuery('<iframe height="1" width="1" style="visibility: hidden" src="'+iframeHref+'"></iframe>').appendTo('body');
                } else {

                    jQuery.each(customParams, function(key, val) {
                        window.cXCustomParams[key] = val;
                    });
                    jQuery.each(userProfileParams, function(key, val) {
                        window.cXUserParams[key] = val;
                    });

                    cX.initializePage();
                    cX.setSiteId(window.cXenseSiteID);
                    cX.setCustomParameters(window.cXCustomParams);
                    cX.setUserProfileParameters(window.cXUserParams);
                    cX.sendPageViewEvent();
                }
            };

            cX.callQueue.push(['setSiteId', cXenseSiteID]);
            cX.callQueue.push(['setCustomParameters', cXCustomParams]);
            cX.callQueue.push(['setUserProfileParameters', cXUserParams]);
            cX.callQueue.push(['sendPageViewEvent', { useAutorefreshCheck: false}]);

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

</script><?php
