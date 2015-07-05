<?php

/**
 * rtWidget Options Page
 *
 * @since rtWidget 1.0
 */
class wpRFPOptions {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action( 'admin_menu', array($this, 'wpRFP_add_plugin_page' ));
        add_action( 'admin_init', array($this, 'wpRFP_page_init' ));
        add_action( 'publish_post', array($this, 'wpRFP_rename_fimg'), 10, 2 );
    }

    /**
     * Add options page
     */
    public function wpRFP_add_plugin_page() {
        global $wpRFP;
        // This page will be under "Settings"
        add_options_page( __( 'Rename Featured Images', $wpRFP->wpRFP_text_domain ), __( 'wpRFP Options', $wpRFP->wpRFP_text_domain ), 'manage_options', 'wpRFP-options', array( $this, 'wpRFP_create_admin_page' ) );
    }

    /**
     * function to rename featured image filename to post title after post publish
     * @param type $post_id
     */
    public function wpRFP_rename_fimg($post_id, $post_single) {
        if (has_post_thumbnail($post_id)) {
            $fpost_id = get_post_thumbnail_id($post_id);
            $fpost_path = get_attached_file($fpost_id);
            $fpost_guid = get_the_guid($fpost_id);
            $fpost_title = sanitize_title($post_single->post_title);
            $fpost_ext = pathinfo($fpost_path, PATHINFO_EXTENSION);
            $fpost_new_path = str_replace(basename($fpost_path, '.'.$fpost_ext), $fpost_title, $fpost_path);
            $fpost_new_guid = str_replace(basename($fpost_guid, '.'.$fpost_ext), $fpost_title, $fpost_guid);
            if (rename($fpost_path, $fpost_new_path)) {
                update_attached_file($fpost_id, $fpost_new_path);
                $my_post = array(
                    'post_title'  => $fpost_title,
                    'post_name'   => $fpost_title,
                    'guid'        => $fpost_new_guid
                );
                // Update the post into the database
                global $wpdb;
                $wpdb->update('wp_posts', $my_post, array('ID'=> $fpost_id));
            }
        }
    }
    
    /**
     * Options page callback
     */
    public function wpRFP_create_admin_page() {
        global $wpRFP;
        
        // Set class property
        $this->options = get_option( 'wpRFP_options' );
        ?>

        <div class="wrap wpRFP-admin">
            <h2><?php _e( 'Rename Featured Images', $wpRFP->wpRFP_text_domain ); ?></h2>
            <?php
            if ((isset($_POST['bulk_rename']) && $_POST['bulk_rename']) || (isset($_POST['force_rename']) && $_POST['force_rename'])) {
                $posts_renamed = array();
                $post_count = isset($_POST['rename_post_count'])? $_POST['rename_post_count'] : '10';
                $start_post_count = isset($_POST['start_post_count'])? $_POST['start_post_count'] : '0';
                $wp_post_status = isset($_POST['wp_post_status'])? $_POST['wp_post_status'] : 'any';
                $post_list = get_posts(array(
                    'post_type' => 'post',
                    'post_status' => $wp_post_status,
                    'posts_per_page' => $post_count,
                    'offset' => $start_post_count,
                    'meta_query' => array(
                        array(
                         'key' => '_thumbnail_id',
                         'compare' => 'EXISTS'
                        ),
                    )
                ));
                $total_count = count($post_list);
                $failed = 0;
                $same_name = 0;
                foreach ($post_list as $post_single) {
                    if (has_post_thumbnail($post_single->ID)) {
                        $fpost_id = get_post_thumbnail_id($post_single->ID);
                        $fpost_path = get_attached_file($fpost_id);
                        $fpost_guid = get_the_guid($fpost_id);
                        $fpost_ext = pathinfo($fpost_path, PATHINFO_EXTENSION);
                        $file_name = basename($fpost_path, '.'.$fpost_ext);
                        $post_name = !empty($post_single->post_title)? sanitize_title($post_single->post_title) : $post_single->post_name;
                        if ($file_name != $post_name || (isset($_POST['force_rename']) && $_POST['force_rename'])) {
                            $fpost_new_path = str_replace(basename($fpost_path, '.'.$fpost_ext), $post_name, $fpost_path);
                            $fpost_new_guid = str_replace(basename($fpost_guid, '.'.$fpost_ext), $post_name, $fpost_guid);
                            if (rename($fpost_path, $fpost_new_path)) {
                                update_attached_file($fpost_id, $fpost_new_path);
                                $my_post = array(
                                    'post_title'  => $post_name,
                                    'post_name'   => $post_name,
                                    'guid'        => $fpost_new_guid
                                );
                                $posts_renamed[] = $post_single->post_title;
                                // Update the post into the database
                                global $wpdb;
                                $wpdb->update('wp_posts', $my_post, array('ID'=> $fpost_id));
                            } else
                                $failed++;
                        } else {
                            $same_name++;
                        }
                    }
                } ?>
                <div class="updated notice notice-success is-dismissible below-h2" id="message">
                    <p><?php echo ($total_count - $failed - $same_name); ?> Featured Images renamed successfully!</p>
                    <?php
                    if ($same_name>0) {
                        echo '<p>' . $same_name . (($same_name>1)? ' images' : ' image') . ' had same name!</p>';
                    } ?>
                    <button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    <?php
                    if (count($posts_renamed)>0) {
                        echo '<p>Post titles of the Posts with renamed featured images:</p>';
                        foreach($posts_renamed as $post_renamed) {
                            echo '<p>' . $post_renamed . '</p>';
                        }
                    }
                    ?>
                </div>
            <?php } ?>
            <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                        <form method="post" action="">
                            <?php settings_fields( 'wpRFP_option_group' ); // This prints out all hidden setting fields ?>
                            <div id="post-body-content" class="postbox">
                                <div title="<?php _e( 'Click to toggle', $wpRFP->wpRFP_text_domain ); ?>" class="handlediv"><br></div>
                                <h3 class="hndle">
                                    <span><?php _e( 'Bulk Rename', $wpRFP->wpRFP_text_domain ); ?></span>
                                </h3>
                                <div class="inside"><?php
                                    do_settings_sections('wpRFP-options');
                                    $args = array(
                                        'post_type'  => 'post',
                                        'posts_per_page' => -1,
                                        'post_status' => 'any',
                                        'meta_query' => array(
                                            array(
                                                'key' => '_thumbnail_id',
                                                'compare' => 'EXISTS'
                                            ),
                                        )
                                    );
                                    $posts_with_fimg = get_posts($args);
                                    $post_count = count($posts_with_fimg);
                                    ?>
                                    <p class="form-field">
                                        <label>Total Posts (with featured images): <?php echo $post_count; ?></label>
                                    </p>
                                    <p class="form-field">
                                        <label>Start Count (offset): </label>
                                        <input name="start_post_count" type="text" value="0" style="width: auto;"/>
                                    </p>
                                    <p class="form-field">
                                        <label>Select Post Type: </label>
                                        <select name="wp_post_status">
                                            <option value="any">All posts</option>
                                            <option value="pending">Pending posts</option>
                                            <option value="publish">Published posts</option>
                                            <option value="draft">Draft posts</option>
                                        </select>
                                    </p>
                                    <p class="form-field">
                                        <label>Select Count: </label>
                                        <select name="rename_post_count">
                                            <option value="1">1</option>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="500">500</option>
                                            <option value="1000">1000</option>
                                            <option value="All">All</option>
                                        </select>
                                    </p>
                                    <input id="bulk_rename" class="button submit" type="submit" name="bulk_rename" value="Bulk Rename">
                                    <input id="force_rename" style="margin-left: 5px;" class="button submit" type="submit" value="Forcefully Rename" name="force_rename">
                                </div>
                            </div>
                        </form>
                    </div> <!-- End of #post-body -->
            </div> <!-- End of #poststuff -->
        </div> <!-- End of wrap wpRFP-admin -->
        <?php
    }

    /**
     * Register and add settings
     */
    public function wpRFP_page_init() {
        register_setting( 'wpRFP_option_group', 'wpRFP_options', array( $this, 'wpRFP_sanitize' ) );
        add_settings_section( 'setting_section_id', '', array( $this, 'wpRFP_print_section_info' ), 'wpRFP-options' );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function wpRFP_sanitize($input) {
        global $wpRFP;
        $new_input = array();
        foreach($wpRFP->rt_widgets as $wpRFP_widget_class ) {
            if ( isset( $input[$wpRFP_widget_class] ) ) {
                $new_input[$wpRFP_widget_class] = $input[$wpRFP_widget_class];
            }
        }
        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function wpRFP_print_section_info() {
        global $wpRFP;
        echo '<p>' . __( 'Rename all featured images to their post titles.', $wpRFP->wpRFP_text_domain ) . '</p>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function wpRFP_print_settings_field( $wpRFP_widget_class ) {
        global $wpRFP;
        $enabled_value = isset( $this->options[$wpRFP_widget_class] ) ? $this->options[$wpRFP_widget_class] : '1';
        
        // Fetch Widget Description
        $class_object = new $wpRFP_widget_class();
        $wpRFP_desc = $class_object->widget_options['description'];
        unset( $class_object ); ?>
        
        <input type='hidden' name="wpRFP_options[<?php echo $wpRFP_widget_class; ?>]" value='0' />
        <input type="checkbox" id='<?php echo $wpRFP_widget_class; ?>' name="wpRFP_options[<?php echo $wpRFP_widget_class; ?>]" <?php checked($enabled_value, '1', TRUE); ?> value='1' />
        <label for="<?php echo $wpRFP_widget_class; ?>"><?php _e( 'Enable Widget', $wpRFP->wpRFP_text_domain ); ?></label>
        <em class="wpRFP_desc"><?php echo $wpRFP_desc; ?></em><?php
    }
}

if ( is_admin() ) {
    $wpRFP_settings_page = new wpRFPOptions ();
}