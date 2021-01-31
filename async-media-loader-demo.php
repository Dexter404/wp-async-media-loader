<?php

/**
 * The plugin bootstrap file
 *
 * Includes all of the dependencies used by the plugin, registers the activation
 * and deactivation functions, and defines a function that starts the plugin.
 *
 * @link              https://github.com/Dexter404/wp-async-media-loader
 * @since             1.0.0
 * @package           AsyncMediaLoaderDemo
 *
 * @wordpress-plugin
 * Plugin Name:       Async Media Loader Demo
 * Plugin URI:        https://github.com/Dexter404/wp-async-media-loader
 * Description:       Plugin to demonstrate asynchrously loading of images in WordPress media library
 * Version:           1.0.0
 * Author:            Rahul Arora
 * Author URI:        https://github.com/Dexter404
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

// Don't load plugin again if already exist one
if (defined('ASYNC_MEDIA_LOADER_DEMO')) {
    die;
}

/**
 * Plugin key.
 */
define('ASYNC_MEDIA_LOADER_DEMO', 'async_media_loader');

/**
 * Currently plugin version.
 */
define('ASYNC_MEDIA_LOADER_VERSION', '1.0.0');


class AsyncMediaLoaderDemo
{
    /**
     * Whether to load async or sync way.
     */
    const ASYNC_MEDIA_LOAD_ENABLED = true;

    /**
     * Limit (in seconds) to timeout media load script.
     * (Default depends on ini_get('max_execution_time') which is 30 secs.)
     */
    const ASYNC_MEDIA_LOAD_TIMEOUT = 30;

    public function __construct()
    {
        $this->custom_post_type = ASYNC_MEDIA_LOADER_DEMO;
        $this->define_admin_hooks();
    }

    /**
     * Define hooks
     */
    private function define_admin_hooks()
    {
        add_action('init', array($this, 'register_custom_post_type'));      // just register our custom post
        add_action('load-edit.php', array($this, 'update_custom_posts'));   // kind of main function for us

        // just for custom post's column view
        add_filter('manage_' . $this->custom_post_type . '_posts_columns', array($this, 'custom_post_columns'), 20);
        add_action('manage_' . $this->custom_post_type . '_posts_custom_column', array($this, 'custom_post_column_value'), 10, 2);
        add_filter('manage_edit-' . $this->custom_post_type . '_sortable_columns', array($this, 'custom_post_sortable_columns'));

        if (self::ASYNC_MEDIA_LOAD_ENABLED) {
            // event to trigger when new custom post added
            add_action('custom_post_added', array($this, 'add_featured_image'), 10, 3);
        }
    }

    /**
     * Register a custom post
     */
    public function register_custom_post_type()
    {
        $labels = array(
            'name'                  => __('Custom Post', $this->custom_post_type),
            'singular_name'         => __('Custom Post', $this->custom_post_type),
            'add_new'               => __('Add New', $this->custom_post_type),
            'all_items'             => __('Custom Post', $this->custom_post_type),
            'add_new_item'          => __('Add New Custom Post', $this->custom_post_type),
            'edit_item'             => __('Edit Custom Post', $this->custom_post_type),
            'new_item'              => __('New Custom Post', $this->custom_post_type),
            'view_item'             => __('View Custom Post', $this->custom_post_type),
            'search_items'          => __('Search Custom Post', $this->custom_post_type),
            'not_found'             => __('No Custom Post Found', $this->custom_post_type),
            'not_found_in_trash'    => __('No Custom Post Found In Trash', $this->custom_post_type),
            'menu_name'             => __('Custom Post', $this->custom_post_type)
        );

        register_post_type(
            $this->custom_post_type,
            array(
                'exclude_from_search'   => true,
                'publicly_querable'     => false,
                'show_in_nav_menus'     => false,
                'public'                => true,
                'show_ui'               => true,
                'query_var'             => $this->custom_post_type,
                'show_in_menu'          => true,
                'show_in_admin_bar'     => false,
                'rewrite'               => false,
                'capabilities'          => array(
                    'create_posts' => false,
                ),
                'map_meta_cap'          => true,
                'supports'              => array('title', 'thumbnail'),
                'labels'                => $labels,
                'menu_icon'             => 'dashicons-images-alt',
            )
        );
    }

    /**
     * Define columns to display in custom post's list view
     */
    public function custom_post_columns($columns)
    {
        $columns = array(
            'cb'        => $columns['cb'],
            'id'        => __('ID', $this->custom_post_type),
            'title'     => __('Title', $this->custom_post_type),
            'status'    => __('Status', $this->custom_post_type),
            'date'      => __('Date', $this->custom_post_type),
        );
        return $columns;
    }

    /**
     * Define row values for above defined columns
     */
    public function custom_post_column_value($column, $custom_post_id)
    {
        if ('id' === $column) {
            echo $custom_post_id;
        }
        if ('status' === $column) {
            echo get_post_meta($custom_post_id, 'loading_status', true);
        }
    }

    /**
     * Define sortable column in list view
     */
    public function custom_post_sortable_columns($columns)
    {
        $columns['id'] = 'id';
        return $columns;
    }


    //====================================================

    /**
     * Lets define some fake content for our custom post
     */
    private function generate_fake_content()
    {
        return array(
            array(
                'title'     => 'post #1',
                'image_url' => 'https://images.freeimages.com/images/small-previews/1ad/tractor-2-1386664.jpg'
            ),
            array(
                'title'     => 'post #2',
                'image_url' => 'https://images.freeimages.com/images/small-previews/371/swiss-mountains-1362975.jpg'
            ),
            array(
                'title'     => 'post #3',
                'image_url' => 'https://images.freeimages.com/images/premium/small-comps/6693/6693200-abstract-artwork.jpg'
            ),
            array(
                'title'     => 'post #4',
                'image_url' => 'https://images.freeimages.com/images/premium/small-comps/4346/43467690-food-icons-and-illustrations-vector-collection.jpg'
            ),
            array(
                'title'     => 'post #5',
                'image_url' => 'https://images.freeimages.com/images/premium/small-comps/8476/8476422-traffic-at-night-iii.jpg'
            ),
        );
    }

    /**
     * Checks whether featured image is in use or not
     */
    private function is_featured_image_in_use($attachment_id)
    {
        // query posts by thumbnail other than our custom post
        $args = array(
            'post_type__not_in'  => $this->custom_post_type,
            'meta_query' => array(
                array(
                    'key'   => '_thumbnail_id',
                    'value' => $attachment_id
                ),
            ),
            'fields'=> 'ids'
        );
        $query = new WP_Query($args);
        return isset($query->posts) && !empty($query->posts);
    }

    /**
     * Add image used in our custom post as featured image
     */
    public function add_featured_image($related_custom_post_id)
    {
        $image_to_load = get_post_meta($related_custom_post_id, 'org_image_url', true);
        if (!isset($image_to_load) || empty($image_to_load)) {
            return;
        }

        $desc = get_the_title($related_custom_post_id);
        
        if (self::ASYNC_MEDIA_LOAD_ENABLED) {
            set_time_limit(self::ASYNC_MEDIA_LOAD_TIMEOUT);

            // Require some Wordpress core files for processing images
            // when using media_sideload_image with WP-Cron
            // ref: https://royduineveld.nl/creating-your-own-wordpress-import/
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attachment_id = media_sideload_image($image_to_load, 0, $desc, 'id');
            // reset php script execution timeout
            if ($l = ini_get('max_execution_time')) {
                set_time_limit($l);
            }
        } else {
            $attachment_id = media_sideload_image($image_to_load, 0, $desc, 'id');
        }

        if (isset($attachment_id) && !is_wp_error($attachment_id)) {
            set_post_thumbnail($related_custom_post_id, $attachment_id);

            // save original url in metadata if needed later on
            add_post_meta($attachment_id, 'custom_post_meta', array(
                'org_image_url' => $org_image_url
            ), true);

            update_post_meta($related_custom_post_id, 'loading_status', 'loaded');
        }
        elseif (is_wp_error($attachment_id)) {
            // log error
            $fp = fopen(plugin_dir_path(__FILE__) . $this->custom_post_type . '_logs.txt', 'a');
            fwrite($fp, print_r("error for post: " . $desc . "\n", true));
            fwrite($fp, print_r($attachment_id->errors, true));
            fclose($fp);
        }
    }

    /**
     * Delete existing content for our custom post
     */
    private function delete_existing_content()
    {
        // deletes all the post with status publish, draft and trash
        $args = array(
            'post_type'     => $this->custom_post_type,
            'post_status'   => 'any',
            'numberposts'   => -1,
            'fields'        => 'ids'
        );
        $custom_post_ids = get_posts($args);
        if (!empty($custom_post_ids)) {
            foreach ($custom_post_ids as $related_custom_post_id) {
                // delete featured image first if exists and not in use
                if (has_post_thumbnail($related_custom_post_id)) {
                    $attachment_id = get_post_thumbnail_id($related_custom_post_id);
                    if (!$this->is_featured_image_in_use($attachment_id)) {
                        wp_delete_attachment($attachment_id, true);
                    }
                }
                wp_delete_post($related_custom_post_id, true);
            }
        }
    }

    /**
     * Loads content as custom post
     */
    private function load_content()
    {
        $json = $this->generate_fake_content();
        foreach ($json as $obj) {
            $new_post = array(
                'post_title'    => $obj['title'],
                'post_type'     => $this->custom_post_type,
                'meta_input'    => array(
                    'org_image_url'  => $obj['image_url'],
                    'loading_status' => 'loading',
                )
            );
            $inserted_post_id = wp_insert_post($new_post);
            if (self::ASYNC_MEDIA_LOAD_ENABLED) {
                // remove existing cron event for this post, then trigger new one
                wp_clear_scheduled_hook('custom_post_added', array($inserted_post_id));
                wp_schedule_single_event(time(), 'custom_post_added', array($inserted_post_id));
            } else {
                $this->add_featured_image($inserted_post_id);
            }
        }

        // reload page after 10 secs
        echo '<meta http-equiv="refresh" content="' . self::ASYNC_MEDIA_LOAD_TIMEOUT . '; url=http://localhost:8888/wordpress/wp-admin/edit.php?post_type=async_media_loader">';
    }

    /**
     * Our main function ;)
     * Work by passing following query params on custom post's admin page:
     * 1. If custom_post_reset=true is passed in url, then delete existing data for our custom post
     * 2. If custom_post_load=true is passed in url, then load data for our custom post
     */
    public function update_custom_posts()
    {
        global $typenow;
        // return if page other than custom post is loaded
        if ($this->custom_post_type != $typenow) {
            return;
        }

        $reset_content = isset($_GET['custom_post_reset']);
        if ($reset_content) {
            $this->delete_existing_content();
        }

        $load_content = isset($_GET['custom_post_load']);
        if ($load_content) {
            $this->load_content();
        }
    }
}

new AsyncMediaLoaderDemo();
