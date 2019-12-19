<div class="wrap">
<?php if ($settings['api_key']) { ?>
        <form method="post" action="<?php echo admin_url('admin.php?page=inkgo'); ?>">
            <h1>InkGo Settings</h1>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="inkgo_key">InkGo API key</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="password" name="inkgo[api_key]" class="input-text regular-input" value="<?php echo esc_attr($settings['api_key']); ?>" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="inkgo_key">Personalize header</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="text" name="inkgo[header]" class="input-text regular-input" value="<?php echo esc_attr($settings['header']); ?>" placeholder="Personalize design" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="inkgo_key">Personalize button</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="text" name="inkgo[button]" class="input-text regular-input" value="<?php echo esc_attr($settings['button']); ?>" placeholder="Personalize Design" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="inkgo_key">Photo label</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="text" name="inkgo[label]" class="input-text regular-input" value="<?php echo esc_attr($settings['label']); ?>" placeholder="Photo" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="printful_key">Confirm button text</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="text" name="inkgo[confirm]" class="input-text regular-input" value="<?php echo esc_attr($settings['confirm']); ?>" placeholder="Confirm" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="printful_key">Change photo button text</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="text" name="inkgo[change]" class="input-text regular-input" value="<?php echo esc_attr($settings['change']); ?>" placeholder="Confirm" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="printful_key">Rotate photo button text</label>
                    </th>
                    <td class="forminp">
                        <fieldset>
                            <input type="text" name="inkgo[rotate]" class="input-text regular-input" value="<?php echo esc_attr($settings['rotate']); ?>" placeholder="Rotate" style="width: 400px;">
                        </fieldset>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="inkgo_settings_hidden" value="Y">
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
            </p>
        </form>
<?php } else { ?>
    <h2 class="nav-tab-wrapper">
        <a href="admin.php?page=inkgo" class="nav-tab nav-tab-active">Connect</a>
    </h2>
    <div id="inkgo-connect">
        <h1><?php esc_html_e('Connect to InkGo', 'inkgo'); ?></h1>
         <img src="<?php echo esc_url(INKGO_PLUGIN_URI.'/assets/images/connect.svg'); ?>" alt="inkgo">
        <?php if ( ! empty( $issues ) ) { ?>
            <p><?php esc_html_e('To connect your store to InkGo, fix the following errors:', 'inkgo'); ?></p>
            <div class="inkgo-warning">
                <ul>
                    <?php
                    foreach ( $issues as $issue ) {
                    echo '<li>' . wp_kses_post( $issue ) . '</li>';
                    }
                    ?>
                </ul>
            </div>
        <?php
            $url = '#';
        } else { ?>
            <p class="connect-description">Please connect your store to InkGo before add product. <a href="https://help.inkgo.io/base/install-inkgo-on-woocommerce/" target="_blank">Read document</a></p><?php
            
            $url = InkGo_Common::get_inkgo_seller_uri() . '/integration/connect?type=woocommerce&shop_name='.get_bloginfo().'&website=' . urlencode(trailingslashit(get_home_url())). '&return_url=' . urlencode(get_admin_url(null, 'admin.php?page=inkgo'));
        }
        echo '<a href="' . esc_url($url) . '" class="button button-primary inkgo-connect-button ' . (!empty($issues) ? 'disabled' : '') . '">' . esc_html__('Connect', 'inkgo') . '</a>';
        ?>
        <img src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ) ?>" class="loader hidden" width="20px" height="20px" alt="loader"/>
    </div>

     <script type="text/javascript">
            jQuery(document).ready(function () {
                InkGo_Connect.init('<?php echo esc_url( admin_url( 'admin-ajax.php?action=ajax_inkgo_check_connect_status' ) ); ?>');
            });
        </script>
<?php } ?>
</div>

