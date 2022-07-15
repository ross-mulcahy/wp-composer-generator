<?php

/**
 * Plugin Name: Composer Generator
 * Description: Create composer.json file from current plugins available on WPackagist.org.
 * Author: Ross Mulcahy
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

function active_site_plugins() {
    $all_plugins = get_plugins();
    $plugin_result="";
        
    foreach($all_plugins as $plugin) {

        $plugin_info = wpdev_get_plugin_data($plugin['TextDomain']);
        
        if($plugin_info){
            $plugin_result .='"wpackagist-plugin/'.$plugin['TextDomain'].'":">='.$plugin['Version'].'",'; 
        }
    }

    $plugin_result = rtrim($plugin_result, ',');

    $result = '
        {
        "name": "acme/composer-plugins",
        "description": "Generate Composer File",
        "repositories":[
        {
        "type":"composer",
        "url":"https://wpackagist.org",
        "only": [
        "wpackagist-plugin/*",
        "wpackagist-theme/*"
        ]
        }
        ],
        "require": {
        '.$plugin_result.'
        },
        "autoload": {
        "psr-0": {
        "Acme": "src/"
        }
        },
        "extra": {
        "installer-paths": {
        "plugins/{$name}/": [
        "type:wordpress-plugin"
        ]
        }
        }
        }';

    echo $result;
 }  

function wpdev_get_plugin_data($urlOrSlug){
    require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
     
    $basename = str_replace('/', '', basename($urlOrSlug));
 
    $info = plugins_api( 'plugin_information', array( 'slug' => $basename ) );
 
    if ( ! $info or is_wp_error( $info ) ) {
        return false;
    }
 
    return $info;
}
 

add_action( 'admin_menu', 'register_cg_admin_page' );


function register_cg_admin_page() {
    add_submenu_page(
        'tools.php',
        __( 'Composer', 'composer_gen' ),
        __( 'Composer', 'composer_gen' ),
        'manage_options',
        'cg-admin',
        'cg_admin_page_contents'
    );
}


function cg_admin_page_contents() {

    ?>
        <h1>
            <?php esc_html_e( 'Composer Generator', 'cg-admin' ); ?>
        </h1>

        <ol>
            <li>Make sure <a href="https://getcomposer.org/" target="_new">Composer</a> is installed.</li>
            <li>Copy the following JSON into a file called composer.json within the wp-content folder.</li>
            <li>Run <code>$ composer update</code>.</li>
            <li>If any missing packages are flagged simply remove them with <code>$ composer remove wpackagist-plugin/plugin-name</code> and run <code>$ composer update</code> again. (Rinse and repeat).</li>
            <li>Use Composer to keep your plugins updated.</li>
        </ol>

        <form action="" method="post">
            <input type="hidden" name="form_submitted" value="1" />
            <input type="submit" value="Generate composer.json"> 
        </form>

    <?php

     if (isset($_POST['form_submitted'])){

    ?>

        <h3>composer.json</h3>

        <textarea id="json" readonly style="height: 600px; width: 90%; font-family: monospace; font-size: 0.8rem; line-height: 1.2;">
       
        </textarea>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var p_data = <?php active_site_plugins(); ?> 
                var textedJson = JSON.stringify(p_data, undefined, 4);
                $('#json').text(textedJson);
            });
        </script>
    <?php

    }

    ?>
        <p>Thanks to <a href="https://getcomposer.org/" target="_new">Composer</a> and <a href="https://wpackagist.org/" target="_new">WordPress Packagist</a>.</p>

    <?php
}
