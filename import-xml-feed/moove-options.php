<?php
/**
 * Moove_Importer_Options File Doc Comment
 *
 * @category  Moove_Importer_Options
 * @package   moove-feed-importer
 * @author    Gaspar Nemes
 */

/**
 * Moove_Importer_Options Class Doc Comment
 *
 * @category Class
 * @package  Moove_Importer_Options
 * @author   Gaspar Nemes
 */
class Moove_Importer_Options
{
    /**
     * Construct
     */
    function __construct()
    {
        add_action('admin_menu', array(&$this, 'moove_importer_admin_menu'));
        add_action('moove_importer_addons_tab_content', array(&$this, 'moove_importer_load_active_tab_view'));
        add_action('moove_importer_buttons', array(&$this, 'moove_importer_buttons'), 10);
        add_action('plugins_loaded', array($this, 'load_languages'));

    }

    function load_languages()
    {

        load_plugin_textdomain('import-xml-feed', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Moove load active tab view
     *
     * @return  void
     */
    function moove_importer_load_active_tab_view($data)
    {

        if ( $data['tab'] == 'feed_importer' ) :
            echo Moove_Importer_View::load('moove.admin.settings.post_type', $data['data']);
        elseif ( $data['tab'] == 'plugin_addons' ):
            echo Moove_Importer_View::load('moove.admin.settings.addons', null);
        elseif ( $data['tab'] == 'plugin_documentation' ):
            echo Moove_Importer_View::load('moove.admin.settings.documentation', null);
        elseif( $data['tab'] == 'plugin_templates' ) :
            $args = array(
                'post_type'         => 'moove_feed_importer',
                'posts_per_page'    => -1,
                'post_status'       => 'publish',
                'orderby'           => 'date',
                'order'             => 'DESC'
            );
            $templates = new WP_Query( $args );
            if ( $templates->have_posts() ) :
                global $post;
                $template_data = array();
                while ( $templates->have_posts() ) : $templates->the_post();
                    $template_data[] = array(
                        'post_id'   => get_the_ID(),
                        'title'     => get_the_title(),
                        'date'      => get_the_date('M j, Y @ G:i'),
                        'slug'      => get_post_field( 'post_name', $post )
                    );
                endwhile;
                wp_reset_query();
                wp_reset_postdata();
            else :
                $template_data = null;
            endif;
            echo Moove_Importer_View::load( 'moove.admin.settings.' . $data['tab'], $template_data );
        elseif ( $data['tab'] == 'template_view' ) :
            $template_id = isset( $_GET['template'] ) ? intval( $_GET['template'] ) : '';
            echo Moove_Importer_View::load( 'moove.admin.settings.' . $data['tab'], array() );
        endif;
    }

    /**
     * Import Feed page added to settings
     *
     * @return  void
     */
    function moove_importer_admin_menu()
    {
        add_options_page('Feed importer', __('Import Feed','import-xml-feed'), 'manage_options', 'moove-importer', array(&$this, 'moove_importer_settings_page'));
    }

    /**
     * Settings page registration
     *
     * @return void
     */
    function moove_importer_settings_page()
    {
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']);
        $data = array();
        if (count($post_types)) :
            foreach ($post_types as $cpt) :
                $taxonomies = get_object_taxonomies($cpt, 'object');
                $data[$cpt] = array(
                    'post_type' => $cpt,
                    'taxonomies' => $taxonomies,
                );
            endforeach;
        endif;
        echo Moove_Importer_View::load('moove.admin.settings.settings_page', $data);
    }

    function moove_importer_buttons()
    {
        ?>
        <a href="#" class="button button-primary moove-start-import-feed"><?php _e('START IMPORT', 'import-xml-feed'); ?></a>

        <hr />
            <span class="moove-importer-template-action">
                <a href="#" class="button button-primary moove-save-as-template-btn">
                    <?php _e( 'Save as template' , 'moove' ); ?>
                </a>

                <a href="#" class="button button-primary moove-importer-load-template-btn">
                    <?php _e( 'Load template' , 'moove' ); ?>
                </a>
                <div class="moove-clearfix"></div>
                <span class="input-holder">
                    <br />
                    <span class="moove-input-group moove-importer-save-template" style="display:none">
                        <input type="text" placeholder="Template name" value="" name="moove_importer_template_name" id="moove_importer_template_name" />
                        <a href="#" class="button button-primary moove-importer-save-template-action">
                            <?php _e( 'Save' , 'moove' ); ?>
                        </a>
                    </span>

                    <span class="moove-input-group moove-importer-load-template" style="display:none">
                        <select name="moove_importer_load_template" id="moove_importer_load_template">
                            <?php
                                $args = array(
                                    'post_type'      => 'moove_feed_importer',
                                    'posts_per_page' => -1,
                                    'post_status'    => 'publish',
                                );
                                $saved_imports = new WP_Query( $args );
                                if ( $saved_imports->have_posts() ) :
                                    while ( $saved_imports->have_posts() ) : $saved_imports->the_post();
                                    ?>
                                        <option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
                                    <?php 
                                    endwhile;
                                    wp_reset_postdata();
                                    wp_reset_query();
                                else : 
                                    ?>
                                        <option value="0">No saved templates</option>
                                    <?php 
                                endif; 
                            ?>
                        </select>
                        <a href="#" class="button button-primary moove-importer-load-template-action">
                            <?php _e( 'Load' , 'moove' ); ?>
                        </a>
                    </span>
                </span>
            </span>
            <div class="moove-clearfix"></div>
            <hr />
            <br />
        <?php
    }
}

$moove_importer_options = new Moove_Importer_Options();
