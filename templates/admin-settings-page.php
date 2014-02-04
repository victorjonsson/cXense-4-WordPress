<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
        <h2>Cxense generella inställningar:</h2>

    <form method="post" action="options.php">
        <?php settings_fields('cxense-settings'); ?>
        <table class="form-table">

            <tr valign="top">
                <th scope="row">CXENSE_API_KEY:</th>
                <td><input type="text" name="<?= CXENSE_API_KEY ?>"
                           value="<?php echo get_option(CXENSE_API_KEY); ?>"/></td>
            </tr>

            <tr valign="top">
                <th scope="row">CXENSE_USER_NAME:</th>
                <td><input type="text" name="<?= CXENSE_USER_NAME ?>"
                           value="<?php echo get_option(CXENSE_USER_NAME); ?>"/></td>
            </tr>

            <tr valign="top">
                <th scope="row">CXENSE_SITE_ID:</th>
                <td><input type="text" name="<?= CXENSE_SITE_ID ?>"
                           value="<?php echo get_option(CXENSE_SITE_ID); ?>"/></td>
            </tr>

        </table>
        <div id="icon-options-general" class="icon32"><br/></div>
        <h2>Widget IDs:</h2>
        <input type="button" class="button-secondary" value="Lägg till ny" onclick="CxenseAdmin.addWidget()" style="margin-top: 5px; margin-bottom: 5px"/>

        <div id="cxense_widgets">
            <? if(get_option(CXENSE_WIDGETS_OPTION) != false){
                $i = 0;
                ?><table class="widefat"> <?
                foreach (get_option(CXENSE_WIDGETS_OPTION) as $widget){
                    ?>
                    <tr>
                        <td><input style="width: 100%" type="button" class="button-secondary" value="Ta Bort" onclick="CxenseAdmin.removeWidget(jQuery(this).parent().parent())"/></td>
                        <td style="width: 45%"><input style="width: 100%" type="text" name="<?= CXENSE_WIDGETS_OPTION ?>[<?=$i?>][key]" value="<?= $widget['key']?>" /></td>
                        <td style="width: 45%"><input style="width: 100%" type="text" name="<?= CXENSE_WIDGETS_OPTION ?>[<?=$i?>][widget_id]" value="<?= $widget['widget_id']?>" /></td>
                        <br/>
                    </tr>
                    <?
                    $i++;
                }
                ?>
                </table>
                <?
            }
            ?>
        </div>

        <?php submit_button(); ?>
    </form>
</div>