<?php

if ( !defined('ABSPATH') || !is_admin() ) {
    header( 'HTTP/1.1 403 Forbidden' );
    exit(   'HTTP/1.1 403 Forbidden' );
}

function cache_control_add_options_submenu_page() {
    add_submenu_page(
        'options-general.php',        // append to Settings sub-menu
        'Cache-Control Options',      // title
        'Cache-Control',              // menu label
        'manage_options',             // required role
        'cache_control',              // options-general.php?page=cache_control
        'cache_control_options_page'  // display page callback
    );
}

add_action( 'admin_menu', 'cache_control_add_options_submenu_page' );

function cache_control_install() {
    global $cache_control_options;
    foreach ($cache_control_options as $key => $option) {
        add_option( 'cache_control_' . $option['id'] . '_max_age', $option['max_age'] );
        add_option( 'cache_control_' . $option['id'] . '_s_maxage', $option['s_maxage'] );
        if ( isset( $option['paged'] ) )
            add_option( 'cache_control_' . $option['id'] . '_paged', $option['paged'] );
}   }

register_activation_hook( __FILE__, 'cache_control_install' );

function cache_control_uninstall() {
    global $cache_control_options;
    foreach ($cache_control_options as $key => $option) {
        delete_option( 'cache_control_' . $option['id'] . '_max_age' );
        delete_option( 'cache_control_' . $option['id'] . '_s_maxage' );
        if ( isset( $option['paged'] ) )
            delete_option( 'cache_control_' . $option['id'] . '_paged' );
}   }

register_uninstall_hook( __FILE__, 'cache_control_uninstall' );

function cache_control_options_page() {
    global $cache_control_options;
    if ( ! isset( $_REQUEST['settings-updated'] ) )
          $_REQUEST['settings-updated'] = false; ?>
 
     <div class="wrap">
           <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
          <?php if ( isset( $_POST ) && count( $_POST ) > 10 ) {
              foreach ($cache_control_options as $key => $option) {
                  $option_keys = array( 'cache_control_' . $option['id'] . '_max_age',
                                        'cache_control_' . $option['id'] . '_s_maxage',
                                        'cache_control_' . $option['id'] . '_paged'
                  );
                  foreach ( $option_keys as $key => $option_key ) {
                      if ( isset( $_POST[$option_key] ) && is_int( intval( $_POST[$option_key] ) ) )
                        update_option( $option_key, intval( $_POST[$option_key] ) );
              }   }   } ?>
          <div id="poststuff">
               <div id="post-body">
                    <div id="post-body-content">
                         <p>You should uninstall this plugin if you’re unfamiliar with the HTTP Cache-Control header! Pages may not be served directly from your WordPress instanece, but rather from client and other caching proxies. Acceptable freshness will vary from website to website so do review the below settings carefully.</p>
                         <form method="post" action="options-general.php?page=cache_control">
                              <?php settings_fields( 'wporg_options' ); ?>
                              <?php $options = get_option( 'wporg_hide_meta' ); ?>
                              <table class="form-table">
                                    <tr>
                                        <th scope="column">Name</th>
                                        <th scope="column">Max-Age</th>
                                        <th scope="column">Shared Max-Age</th>
                                        <th scope="column">Pagination</th>
                                    </tr>
                                    <?php foreach ($cache_control_options as $key => $option) { ?>
                                    <tr>
                                        <th scope="row"><?php print $option['name'] ?></th>
                                        <td><input type="number" name="cache_control_<?php print $option['id']; ?>_max_age" id="cache_control_<?php print $option['id']; ?>_max_age" value="<?php print get_option( 'cache_control_' . $option['id'] . '_max_age', $option['max_age'] ) ?>"></td>
                                        <td><input type="number" name="cache_control_<?php print $option['id']; ?>_s_maxage" id="cache_control_<?php print $option['id']; ?>_s_maxage" value="<?php print get_option( 'cache_control_' . $option['id'] . '_s_maxage', $option['s_maxage'] ) ?>"></td>
                                        <?php if ( isset( $option['paged'] ) ) { ?>
                                        <td><input type="number" name="cache_control_<?php print $option['id']; ?>_paged" id="cache_control_<?php print $option['id']; ?>_paged" value="<?php print get_option( 'cache_control_' . $option['id'] . '_paged', $option['paged'] ) ?>"></td>
                                        <?php } else { ?><td></td><?php } ?>
                                    </tr>
                                    <?php } ?>
                              </table>
                              <input type="submit" value="Save" class="button-primary">
                              <?php if ( isset( $_POST ) && count( $_POST ) > 10 ) { ?><p style="color:green;display:inline;margin-left: 16px;">Saved.</p> <?php } ?>
                         </form>
                         <p>Control the freshness communicated in the Cache-Control header on different types of pages. Every value is <strong>in seconds</strong>. <em>Shared Max Age</em> is any reverse cashing or other public proxy servers, and is generally set lower than <em>Max Age</em>. Set the freshness as high as is acceptable for your website! Longer expiration days reduce the load on WordPress as clients can be served stale static copies from reverse caching proxies. <em>Pagination</em> adds this many seconds linearly increasing to older pages (e.g. page four is assigned <em>$max_age or $shared_max_age + ( 4 × $per_page )</em>). This is useful when oler pages don’t have the same freshness requirement as newer pages. Pagination factor is limited to ten times that of the base Max-Age/Shared Max-Age. <em>Dated archives</em> is only used for stale periods (previous years, months, etc.), where as the current period share settings with <em>Main index</em>. Set any field to 0 to disable it. Note that proxies will use <em>Max Age</em> if <em>Shared Max Age</em> is unset.</p>
                         <p>Example header: <em>Cache-Control: max-age=$max_age, s_maxage=$shared_max_age</em></p>
                         <p>The header is only added to dynamic pages generated by WordPress. You should also set Cache-Control headers manually in your webserver configuration for images, scripts, stylesheets, and other static resources.</p>
                         <p>If you’re <em>scheduling posts in the future,</em> you can safely set long cache times as dynamic pages (indexes, feeds, taxonemies, searches, others.) will dynamically reduce their caching times to a few seonds after a scheduled post is published.</p>
                    </div>
               </div>
          </div>
     </div>
     <style>.form-table {width: auto;} tr td:nth-of-type(1) input {width: 120px;} tr td:nth-of-type(2) input {width: 100px;} tr td:nth-of-type(3) input {width: 80px;} th[scope="column"]:not(:first-of-type) {padding: 15px 10px; width: auto;}</style>
<?php } ?>
