<div class="wrap">

    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>cXense 4 WordPress</h2>

    <form method="post" action="options.php">
        <?php
        settings_fields('cxense-settings');
        do_settings_sections('cxense-settings');
        ?>

        <div id="icon-options-general" class="icon32"><br/></div>
        <h2>Widget IDs:</h2>


        <div id="cxense_widgets">
            <table class="widefat">
            <?php if( $widget_opts = cxense_get_opt('cxense_widgets_options') ){
                $i = 0;
                foreach ($widget_opts as $widget) {
                    ?>
                    <tr>
                        <td><input style="width: 100%" type="button" class="button-secondary" value="Ta Bort" onclick="CxenseAdmin.removeWidget(jQuery(this).parent().parent())"/></td>
                        <td style="width: 45%"><input style="width: 100%" type="text" name="cxense_widgets_options[<?= $i ?>][key]" value="<?= $widget['key'] ?>" /></td>
                        <td style="width: 45%"><input style="width: 100%" type="text" name="cxense_widgets_options[<?= $i ?>][widget_id]" value="<?= $widget['widget_id'] ?>" /></td>
                        <br/>
                    </tr>
                    <?php
                    $i++;
                }
            }
            ?>
            </table>
        </div>

        <input type="button" class="button-secondary" value="Lägg till ny" onclick="CxenseAdmin.addWidget()" style="margin-top: 12px; margin-bottom: 5px"/>

        <?php submit_button(); ?>

    </form>
</div>
