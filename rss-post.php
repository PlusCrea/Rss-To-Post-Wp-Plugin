<?php
/*
Plugin Name:Rss Post Plugin
Plugin URI:
Description: Insert post from Rss
Version: 1.0
Author: Ali YAKAR
Author URI: http://pluscrea.net
License: GPLv2
*/



//Add sublink under the Settings
add_action("admin_menu", "rss_addsublink");

function rss_addsublink()
{
    add_submenu_page(
        'options-general.php',
        'Rss Post',
        'Rss Post',
        'administrator',
        'Rss-Post',
        'rss_config_page'
    );
}

function rss_config_page()
{

    if (($_POST["action"] == "formupdate")  || wp_verify_nonce($_POST['name_of_nonce_field'], 'name_of_my_action')) {

        include_once(ABSPATH . WPINC . '/feed.php');

        $linkarr = array(
            "https://news.autojournal.fr/rss.xml",
            "https://www.automobile-magazine.fr/toute-l-actualite/rss.xml"
        );


        global $wpdb;
        global $post;

        foreach ($linkarr  as &$link) {
            // Get a SimplePie feed object from the specified feed source.
            $rss = fetch_feed($link);

            $maxitems = 0;

            if (!is_wp_error($rss)) : // Checks that the object is created correctly

                // Figure out how many total items there are, but limit it to 5. 
                $maxitems = $rss->get_item_quantity(150);

                // Build an array of all the items, starting with element 0 (first element).
                $rss_items = $rss->get_items(0, $maxitems);
            //print_r($rss_items);
            endif;


?>

            <ul>
                <?php if ($maxitems == 0) : ?>
                    <li><?php _e('No items', 'my-text-domain'); ?></li>
                <?php else : ?>
                    <?php // Loop through each feed item and display each item as a hyperlink. 
                    ?>
                    <?php foreach ($rss_items as $item) : ?>
                        <li>
                            <a href="<?php echo esc_url($item->get_permalink()); ?>" title="<?php printf(__('Posted %s', 'my-text-domain'), $item->get_date('j F Y | g:i a')); ?>">
                                <?php echo esc_html($item->get_title()); ?>
                            </a> <?php echo $item->get_description(); ?>
                        </li>
                    <?php

                        $querystr = "
                SELECT $wpdb->posts.* 
                FROM $wpdb->posts
                WHERE $wpdb->posts.pinged = '" .  $item->get_permalink() . "'";

                        $category = get_cat_ID('Blog');

                        $pageposts = $wpdb->get_results($querystr, OBJECT);
                        if (!$pageposts) {
                            // Create post object
                            $my_post = array(
                                'post_title'    => wp_strip_all_tags($item->get_title()),
                                'post_content'  => $item->get_description(),
                                'post_status'   => 'publish',
                                'post_author'   => 1,
                                'post_category' => array($category),
                                'post_date' => $item->get_date('Y-m-d H:i:s'),
                                'post_password' => $item->get_permalink()
                            );

                            // Insert the post into the database
                            wp_insert_post($my_post);
                        } else echo "var";

                    endforeach; ?>
                <?php endif; ?>
            </ul>
    <?php
        }
    }

    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="formupdate">
        <?php
        //wp_nonce_field('sml_action', 'sml_nonce_field');
        ?>
        <input type="submit" value="Submit" class="button-primary" /></td>

    </form>
<?php
}
