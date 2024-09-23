<?php
/**
 * Moove_Controller File Doc Comment
 *
 * @category  Moove_Controller
 * @package   moove-feed-importer
 * @author    Gaspar Nemes
 */

/**
 * Moove_Controller Class Doc Comment
 *
 * @category Class
 * @package  Moove_Controller
 * @author   Gaspar Nemes
 */
class Moove_Importer_Controller {
	/**
     * xml content variable
     */
    private $xmlreturn = null;
    /**
     * Construct function
     */
	function __construct() {
        $this->xmlreturn = array();
        $this->xmlnodes = array();
        add_action( 'moove_importer_sanitize_xml', array( &$this, 'moove_importer_sanitize_xml' ), 5, 1 );
        add_action( 'moove_importer_check_other_taxonomies', array( &$this, 'moove_importer_check_taxonomies' ), 5, 3 );
        add_action( 'moove_importer_addons_tabs', array( &$this, 'moove_importer_add_templates_tab' ), 5, 2 );
        add_action( 'moove_importer_check_extensions', array( &$this, 'moove_importer_check_extensions' ), 10, 1 );
        add_action( 'init', array( &$this, 'moove_feed_importer_cpt' ), 0 );
 	}

    // Register Custom Post Type
    function moove_feed_importer_cpt() {

        $labels = array(
            'name'                  => _x( 'Manage Imports', 'Post Type General Name', 'sage' ),
            'singular_name'         => _x( 'Manage Import', 'Post Type Singular Name', 'sage' ),
            'menu_name'             => __( 'Manage Import', 'sage' ),
            'name_admin_bar'        => __( 'Manage Import', 'sage' ),
            'archives'              => __( 'Item Archives', 'sage' ),
            'attributes'            => __( 'Item Attributes', 'sage' ),
            'parent_item_colon'     => __( 'Parent Item:', 'sage' ),
            'all_items'             => __( 'All Items', 'sage' ),
            'add_new_item'          => __( 'Add New Item', 'sage' ),
            'add_new'               => __( 'Add New', 'sage' ),
            'new_item'              => __( 'New Item', 'sage' ),
            'edit_item'             => __( 'Edit Item', 'sage' ),
            'update_item'           => __( 'Update Item', 'sage' ),
            'view_item'             => __( 'View Item', 'sage' ),
            'view_items'            => __( 'View Items', 'sage' ),
            'search_items'          => __( 'Search Item', 'sage' ),
            'not_found'             => __( 'Not found', 'sage' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'sage' ),
            'featured_image'        => __( 'Featured Image', 'sage' ),
            'set_featured_image'    => __( 'Set featured image', 'sage' ),
            'remove_featured_image' => __( 'Remove featured image', 'sage' ),
            'use_featured_image'    => __( 'Use as featured image', 'sage' ),
            'insert_into_item'      => __( 'Insert into item', 'sage' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'sage' ),
            'items_list'            => __( 'Items list', 'sage' ),
            'items_list_navigation' => __( 'Items list navigation', 'sage' ),
            'filter_items_list'     => __( 'Filter items list', 'sage' ),
        );
        $args = array(
            'label'                 => __( 'Manage Import', 'sage' ),
            'description'           => __( 'Manage Imports', 'sage' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'custom-fields' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => false, // false
            'show_in_menu'          => false, // false
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
        );
        register_post_type( 'moove_feed_importer', $args );

    }

    function moove_importer_check_extensions( $content ) {
        return class_exists('Moove_Importer_Template_Actions') ? '' : $content;
    }

    function moove_importer_add_templates_tab(  $tabs, $active_tab  ) {
        ob_start();
        ?>
        <a href="?page=moove-importer&tab=plugin_templates" class="nav-tab <?php echo $active_tab == 'plugin_templates' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Templates','moove'); ?>
        </a>
        <?php
        echo apply_filters('moove_importer_check_extensions',ob_get_clean());
    }

    function moove_importer_load_active_tab_view( $data ) {
        $return_data = '';
        if( $data['tab'] == 'plugin_templates' ) :
            $return_data =  Moove_Importer_View::load( 'moove.admin.settings.' . $data['tab'], array() );
        endif;
        echo apply_filters('moove_importer_check_extensions', $return_data);
    }

    function get_plugin_details( $plugin_slug = '' ) {
        $plugin_return   = false;
        $wp_repo_plugins = '';
        $wp_response     = '';
        $wp_version      = get_bloginfo( 'version' );
        if ( $plugin_slug && $wp_version > 3.8 ) :
            $args        = array(
                'author' => 'MooveAgency',
                'fields' => array(
                    'downloaded'      => true,
                    'active_installs' => true,
                    'ratings'         => true,
                ),
            );
            $wp_response = wp_remote_post(
                'http://api.wordpress.org/plugins/info/1.0/',
                array(
                    'body' => array(
                        'action'  => 'query_plugins',
                        'request' => serialize( (object) $args ),
                    ),
                )
            );
            if ( ! is_wp_error( $wp_response ) ) :
                $wp_repo_response = unserialize( wp_remote_retrieve_body( $wp_response ) );
                $wp_repo_plugins  = $wp_repo_response->plugins;
            endif;
            if ( $wp_repo_plugins ) :
                foreach ( $wp_repo_plugins as $plugin_details ) :
                    if ( $plugin_slug == $plugin_details->slug ) :
                        $plugin_return = $plugin_details;
                    endif;
                endforeach;
            endif;
        endif;
        return $plugin_return;
    }

    function moove_importer_check_taxonomies( $taxonomies, $post_types, $acf_groups ) {
        echo $taxonomies;
    }
    /**
     * Recursive function to read XML nodes
     * @param  object $xml     XML object.
     * @param  string $parent  Parent string.
     * @return int $child_count
     */
    private function moove_recurse_xml( $xml , $parent = "" ) {
        $child_count = 0;
        foreach( $xml as $key => $value ) :
            $child_count++;
            if ( count($value) ) :
                $name = $value->getName();
                $count = isset( $this->xmlnodes[$name] ) && isset( $this->xmlnodes[$name]['count'] ) ? $this->xmlnodes[$name]['count'] + 1 : 0;
                $this->xmlnodes[$name] = array(
                    'count'         =>  $count,
                    'name'          =>  $name,
                    'attributes'    =>  $value->attributes(),
                    'key'           =>  $parent . "/" . (string)$key
                );
            endif;
            // No childern, aka "leaf node".
            if( Moove_Importer_Controller::moove_recurse_xml( $value , $parent . "/" . $key ) == 0 ) {
                $this->xmlreturn[] = array(
                    'key'           =>  $parent . "/" . (string)$key,
                    'attributes'    =>  $value->attributes(),
                    'value'         =>  maybe_unserialize( htmlspecialchars( $value ) )
                );
            }
        endforeach;
       return $child_count;
    }

    public function moove_importer_sanitize_xml( $xml ) {
        return $xml;
    }
    /**
     * Work with the xml file
     * @param  array $args Fields from AJAX Post.
     * @return mixt
     */
    public function moove_importer_get_content( $url ) {
        /* gets the data from a URL */

        $ch = curl_init();
        $timeout = 5;
        $user_agent = "Mozilla/5.0 (X11; Linux i686; rv:24.0) Gecko/20140319 Firefox/24.0 Iceweasel/24.4.0";

        curl_setopt( $ch, CURLOPT_URL, esc_url( $url,  array( 'http', 'https', 'feed' ) ) );
        curl_setopt( $ch, CURLOPT_USERAGENT,$user_agent) ;
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER,true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION,true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, 1 );   
        curl_setopt( $ch, CURLOPT_COOKIEFILE, '' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_ENCODING, 'UTF-8' );
        $data = curl_exec($ch);

        $errors = curl_error($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $data;
    }
    public function moove_read_xml( $args ) {
        $return_array = array();
        $parent       = false;
        if ( $args['type'] === 'url' ) :
            $feed_url = $args['data'];
            $feed_url = esc_url( $feed_url,  array( 'http', 'https', 'feed' ) );
            if ( $feed_url ) :
                $xml_string = Moove_Importer_Controller::moove_importer_get_content( $feed_url );
                $xml_string = htmlspecialchars_decode( $xml_string );
                $xml_string = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $xml_string);
                $xml_string = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $xml_string );
                $xml = simplexml_load_string( $xml_string );
            endif;
        else :
            $xml = simplexml_load_string( wp_unslash( $args['data'] ) );
        endif;

        $xml = apply_filters( 'moove_importer_sanitize_xml', $xml );


        if ( $args['xmlaction'] === 'check' ) :
            if ( $xml ) :
                $parent = $parent . "/" . $xml->getName();                
                Moove_Importer_Controller::moove_recurse_xml( $xml, $parent );                
                // $this->xmlnodes = array_unique( $this->xmlnodes );
                ob_start(); ?>
                <h4><?php _e( 'Select your repeated XML element you want to import', 'import-xml-feed' ); ?></h4>
                <select name="moove-xml-nodes" id="moove-xml-nodes" class="moove-xml-nodes">
                    <?php
                    $first_node_select = "";
                    foreach ( $this->xmlnodes as $nodekey => $nodecount ) : ?>
                        <?php if ( $first_node_select == '' ) : $first_node_select = $nodecount['key']; endif; ?>
                        <option value="<?php echo $nodecount['key']; ?>">
                            <?php echo $nodekey.' ('.$nodecount['count'].') '.$nodecount['key'].''; ?>
                        </option>
                    <?php
                    endforeach;
                    ?>
                </select>
                <br / >
                <br / >
                <?php
                return json_encode(
                    array(
                        'select_nodes'      =>  ob_get_clean(),
                        'selected_element'  =>  $first_node_select,
                        'response'          =>  'true'
                    )
                );
            else :
                return json_encode( array( 'response' => 'false' ) );
            endif;

        elseif ( $args['xmlaction'] === 'import' ) :
            $return_array['node_count'] = count( $xml );
            if ( count( $xml ) ) :
                foreach ( $xml as $key => $value ) :
                    Moove_Importer_Controller::moove_recurse_xml( $value );
                    
                    $return_array['data'][]= $this->xmlreturn;
                    $this->xmlreturn = array();
                endforeach;
            endif;
            return true;
        elseif ( $args['xmlaction'] === 'preview' ) :
            $selected_node = $args['node'];

            $xxml = $xml;
            if ( $xml->getNamespaces(true) ) :
                $xml->registerXpathNamespace( 'atom' , 'http://www.w3.org/2005/Atom' );
                $selected_node = str_replace( "/" , "/atom:" , $selected_node );
            endif;

            $xml = $xml->xpath( "$selected_node" );
            if ( count( $xml ) ) :
                ob_start();                
                echo "<hr><h4>Node count: ". count( $xml )." <span class='pagination-info'> 1 / " . count( $xml ) . " </span></h4>";
                if ( count( $xml ) > 1 ) :
                    echo "<span data-current='1'>";
                    echo "<a href='#' class='moove-xml-preview-pagination button-previous button-disabled'>Previous</a>";
                    echo "<a href='#' class='moove-xml-preview-pagination button-next'>Next</a>";
                    echo "</span>";
                endif;
                echo "<hr>";
                $i = 0;
                $return_keys = array();
                $readed_data = array();
                foreach ( $xml as $key => $value ) :
                    $i++;
                    Moove_Importer_Controller::moove_recurse_xml( $value );
                    if ( $value->attributes() ) :
                        $this->xmlreturn[] = array(
                            'key'           =>  '/'.$value->getName(),
                            'attributes'    =>  $value->attributes(),
                            'value'         =>  ''
                        );
                    endif;
                    if ( $i > 1 ) : $hidden_class = 'moove-hidden'; else : $hidden_class = 'moove-active'; endif;
                    echo "<div class='moove-importer-readed-feed $hidden_class' data-total='".count( $xml )."' data-no='$i'>";
                    $node_attributes = $value->attributes();
                    if ( $node_attributes && class_exists('Moove_Importer_Template_Actions') ) :
                        $attributes = json_decode( json_encode( $node_attributes ), true );

                        if ( isset( $attributes['@attributes'] ) && ! empty( $attributes['@attributes'] ) ) : 
                            $node_attributes = array();                           
                            $node_attributes['attributes'] = $attributes['@attributes'];
                            foreach ( $attributes['@attributes'] as $attr_key => $attr_val ) :
                                $return_keys[] = '/'.$value->getName() . '/@' . $attr_key;
                            endforeach;                                  
                            ?>                            
                            <?php                            
                        endif;
                    endif;

                    foreach ( $this->xmlreturn as $xmlvalue ) :
                        $return_keys[] = $xmlvalue['key'];
                        if ( isset( $xmlvalue['attributes'] ) && ! empty( $xmlvalue['attributes'] ) && class_exists('Moove_Importer_Template_Actions')  ) :
                            foreach ( $xmlvalue['attributes'] as $attr_key => $attr_val ) :
                                $return_keys[] = $xmlvalue['key'] . '/@' . $attr_key;
                            endforeach;
                        endif;
                        $readed_data[ $i ]['values'][] = array(
                            'key'           =>  $xmlvalue['key'],
                            'attributes'    =>  $xmlvalue['attributes'],
                            'value'         =>  $xmlvalue['value']
                        );?>
                        <p>
                            <strong>
                                <?php echo $xmlvalue['key']; ?>:
                            </strong>
                            <?php echo $xmlvalue['value']; ?>

                        </p>

                        <?php 
                            do_action( 'moove_importer_get_attribues', $xmlvalue );                             
                        ?>
                    <?php

                    endforeach;   
                    $this->xmlreturn = null;
                    echo "</div>";
                endforeach;
                $return_keys = array_unique( $return_keys );
                $return_keys = apply_filters( 'moove_importer_sanitize_return_keys', $return_keys );
                if ( count( $return_keys ) ) :
                    $select_options = "<option value='0'>Select a field</option>";
                    $_xml = $xml;
                    foreach ( $return_keys as $select_value ) :
                        $select_options .= "<option value='" . $select_value . "'>" . $select_value . "</option>";
                    endforeach;
                endif;
                return json_encode(
                    array(
                        'content'           =>  ob_get_clean(),
                        'select_option'     =>  $select_options,
                        'xml_json_data'     =>  json_encode( $readed_data )
                    )
                );
            else :
                $selected_node = $args['node'];
                $xml = $xxml->xpath( "$selected_node" );
                ob_start();

                echo "<hr><h4>Node count: ". count( $xml )." <span class='pagination-info'> 1 / " . count( $xml ) . " </span></h4>";
                if ( count( $xml ) > 1 ) :
                    echo "<span data-current='1'>";
                    echo "<a href='#' class='moove-xml-preview-pagination button-previous button-disabled'>Previous</a>";
                    echo "<a href='#' class='moove-xml-preview-pagination button-next'>Next</a>";
                    echo "</span>";
                endif;
                echo "<hr>";
                $i == 0;
                $return_keys = array();
                $readed_data = array();
                foreach ( $xml as $key => $value ) :
                    $i++;
                    Moove_Importer_Controller::moove_recurse_xml( $value );
                    if ( $value->attributes() ) :
                        $this->xmlreturn[] = array(
                            'key'           =>  '/'.$value->getName(),
                            'attributes'    =>  $value->attributes(),
                            'value'         =>  ''
                        );
                    endif;
                    $node_attributes = $value->attributes();
                    if ( $node_attributes ) :
                        $attributes = json_decode( json_encode( $node_attributes ), true );

                        if ( isset( $attributes['@attributes'] ) && ! empty( $attributes['@attributes'] ) ) : 
                            $node_attributes = array();                           
                            $node_attributes['attributes'] = $attributes['@attributes'];
                            foreach ( $attributes['@attributes'] as $attr_key => $attr_val ) :
                                $return_keys[] = '/'.$value->getName() . '/@' . $attr_key;
                            endforeach;                                  
                            ?>                            
                            <?php                            
                        endif;
                    endif;
                    if ( $i > 1 ) : $hidden_class = 'moove-hidden'; else : $hidden_class = 'moove-active'; endif;
                    echo "<div class='moove-importer-readed-feed $hidden_class' data-total='".count( $xml )."' data-no='$i'>";                    
                    foreach ( $this->xmlreturn as $xmlvalue ) :
                        $return_keys[] = $xmlvalue['key'];
                        if ( isset( $xmlvalue['attributes'] ) && ! empty( $xmlvalue['attributes'] ) && class_exists('Moove_Importer_Template_Actions')  ) :
                            foreach ( $xmlvalue['attributes'] as $attr_key => $attr_val ) :
                                $return_keys[] = $xmlvalue['key'] . '/@' . $attr_key;
                            endforeach;
                        endif;
                        $readed_data[ $i ]['values'][] = array(
                            'key'           =>  $xmlvalue['key'],
                            'attributes'    =>  $xmlvalue['attributes'],
                            'value'         =>  $xmlvalue['value']
                        );?>
                        <p>
                            <strong>
                                <?php echo $xmlvalue['key']; ?>:
                            </strong>
                            <?php echo $xmlvalue['value']; ?>
                        </p>

                        <?php if ( $xmlvalue['attributes'] ) : ?>
                        <?php do_action( 'moove_importer_get_attribues', $xmlvalue ); ?>
                        <?php endif; ?>
                    <?php
                    endforeach;
                    $this->xmlreturn = null;
                    echo "</div>";
                endforeach;
                $return_keys = array_unique( $return_keys );
                $return_keys = apply_filters( 'moove_importer_sanitize_return_keys', $return_keys );
                if ( count( $return_keys ) ) :
                    $select_options = "<option value='0'>Select a field</option>";
                    $_xml = $xml;
                    foreach ( $return_keys as $select_value ) :
                        $select_options .= "<option value='" . $select_value . "'>" . $select_value . "</option>";
                    endforeach;
                endif;
                return json_encode(
                    array(
                        'content'           =>  ob_get_clean(),
                        'select_option'     =>  $select_options,
                        'xml_json_data'     =>  json_encode( $readed_data )
                    )
                );
            endif;
        endif;
    }
    /**
     * Searches for $needle in the multidimensional array $haystack.
     *
     * @param mixed $needle The item to search for.
     * @param array $haystack The array to search.
     * @return array|bool The indices of $needle in $haystack across the
     *  various dimensions. FALSE if $needle was not found.
     */
    private function moove_recursive_array_search($needle,$haystack) {
        foreach( $haystack as $key => $value ) :
            if( $needle === $value ) :
                return array( $key );
            else :
                if ( is_array( $value ) && $subkey = Moove_Importer_Controller::moove_recursive_array_search( $needle , $value ) ) :
                    array_unshift( $subkey, $key );
                    return $subkey;
                endif;
            endif;
        endforeach;
    }
    /**
     * Return an ID of an attachment by searching the database with the file URL.
     *
     * First checks to see if the $url is pointing to a file that exists in
     * the wp-content directory. If so, then we search the database for a
     * partial match consisting of the remaining path AFTER the wp-content
     * directory. Finally, if a match is found the attachment ID will be
     * returned.
     *
     * @param string $url The URL of the image (ex: http://mysite.com/wp-content/uploads/2013/05/test-image.jpg).
     *
     * @return int|null $attachment Returns an attachment ID, or null if no attachment is found
     */
    private function moove_get_attachment_id_from_src( $url ) {
        $attachment_id = 0;
        $dir = wp_upload_dir();
        $file = basename( $url );
        $query_args = array(
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'fields'      => 'ids',
            'meta_query'  => array(
                array(
                    'value'   => $file,
                    'compare' => 'LIKE',
                    'key'     => '_wp_attachment_metadata',
                ),
            )
        );
        $query = new WP_Query( $query_args );
        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post_id ) :
                $meta = wp_get_attachment_metadata( $post_id );
                $original_file       = basename( $meta['file'] );
                $cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
                if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) :
                    $attachment_id = $post_id;
                    break;
                endif;
            endforeach;
            wp_reset_query();
            wp_reset_postdata();
        }
        return $attachment_id;
    }

    /**
     * Upload image, and set as featured image
     * @param  int $post_id   Assign as featured image for this post.
     * @param  string $image_url Image URL from the feed
     * @return void
     */
    private function moove_set_featured_image( $post_id, $image_url, $set_as_thumbnail ) {
        // Add Featured Image to Post.
        $attach_id      = false;        
        $upload_dir     = wp_upload_dir(); // Set upload folder.
        
        if ( substr( $image_url, 0, 2) == '//' ) :
            $image_url = 'https:' . $image_url;
        endif;
       
        $filename       = basename( $image_url ); // Create image file name.
        $wp_filetype    = wp_check_filetype( $filename );        

        // validating filetype, wp_check_filetype function validates the mime types, returns false if the file doesn’t match a supported mime type.
        if ( $wp_filetype && isset( $wp_filetype['type'] ) && $wp_filetype['type'] ) {     

            // Check folder permission and define file location.
            if( wp_mkdir_p( $upload_dir['path'] ) ) :
                $file = $upload_dir['path'] . '/' . $filename;
            else :
                $file = $upload_dir['basedir'] . '/' . $filename;
            endif;

            if( $file && !file_exists( $file ) ) :
                $image_data = file_get_contents($image_url); // Get image data.

                // Create the image  file on the server.
                file_put_contents( $file, $image_data );                

                // Checking if the file is a valid image.
                if ( file_is_valid_image( $file ) ) :
                    // Set attachment data.
                    $attachment = array(
                        'post_mime_type'    =>  $wp_filetype['type'],
                        'post_title'        =>  sanitize_file_name( $filename ),
                        'post_content'      =>  '',
                        'post_status'       =>  'inherit'
                    );
                    // Create the attachment.
                    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
                    // Include image.php
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    // Define attachment metadata.
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                    // Assign metadata to attachment.
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                endif;
            else :
                // Searching for attachement ID.
                $attach_id = Moove_Importer_Controller::moove_get_attachment_id_from_src( $file );
            endif;

            // And finally assign featured image to post.
            if ( $set_as_thumbnail && $attach_id ) :
                set_post_thumbnail( $post_id, $attach_id );
            endif;
        }
        return $attach_id;
    }

    /**
     * Place ACF custom fields to post
     * @param  array $args Custom data
     * @return boolean True if the post was created successfully, and False if not.
     */
    public function moove_instert_acf_fields( $args, $post_id ) {
        if ( $args && ! empty( $args ) ) :
            $supported_types = array(
                'text',
                'number',
                'textarea',
                'email',
                'password',
                'wysiwyg',
                'image',
                'date_picker',
                'color_picker'
            );
            foreach ( $args['acf'] as $form_key => $acf_value ) :
                if ( in_array( $acf_value['type'], $supported_types ) ) :
                    $key    = $acf_value['key'];
                    $value  = $acf_value['value'];
                    if ( function_exists( 'update_field' ) ) :
                        switch ($acf_value['type']) {
                            case 'number':
                                $value = intval( $value );
                                break;
                            case 'email':
                                $value = sanitize_email( $value );
                                break;
                            case 'image':
                                $attachment_id = Moove_Importer_Controller::moove_set_featured_image( $post_id, $value, false );
                                $value = intval( $attachment_id );
                                break;
                            case 'date_picker' :
                                try {
                                    $value = DateTime::createFromFormat( "D, d M Y H:i:s O", $value )->format('Ymd');
                                } catch (Exception $e) {
                                    $value = '';
                                }
                            default:
                                $value = sanitize_text_field( $value );
                        }
                        if ( $value ) :
                            update_field( $key, $value, $post_id );
                        endif;
                    endif;
                endif;
            endforeach;
            return true;
        endif;
        return false;
    }

    /**
     * Place custom fields to post
     * @param  array $args Custom data
     * @return boolean True if the post was created successfully, and False if not.
     */
    public function moove_instert_custom_fields( $args, $post_id ) {
        if ( $args && ! empty( $args ) ) :
            foreach ( $args['customfields'] as $form_key => $customfield_value ) :
                add_post_meta( $post_id, sanitize_text_field($customfield_value['field']), $customfield_value['value'] );
            endforeach;
            return true;
        endif;
        return false;
    }


    /**
     * Create post from $args data
     * @param  array $args Custom data
     * @return boolean True if the post was created successfully, and False if not.
     */
    public function moove_create_post( $args ) {
        $form_data = $args['form_data'];
        $key = json_decode( wp_unslash( $args['key'] ) );
        $xml_data_values = $args['value'];
        $new_form_data = array();
        $acf_form_data = array();
        $customfields_data = array();
        foreach ( $form_data as $form_key => $form_value ) :

            if ( $form_value !== '0' && $form_key !== 'post_status' && $form_key !== 'post_type' && $form_key !== 'post_author' && $form_key !== 'post_featured_image' ) :

                if ( $form_key === 'taxonomies' && is_array( $form_value ) ) :
                    $j = 0;
                    foreach ( $form_value as $tax_key => $tax_value ) :
                        if ( $tax_value['title'] !== '0' ) :
                            $j++;
                            $title_explode = explode( '/@', $tax_value['title'] );
                            if ( count ( $title_explode ) > 1 ) :
                                $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0] , $xml_data_values['values']) ;
                                if ( is_array( $_key ) ) :
                                    $tax_title = $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]];
                                    $new_form_data[ $form_key ][] = array(
                                        'taxonomy'      =>  $tax_value['taxonomy'],
                                        'title'         =>  $tax_title,
                                    );
                                endif;
                            else :
                                $_key =  Moove_Importer_Controller::moove_recursive_array_search( $tax_value['title'] , $xml_data_values['values']) ;
                                if ( is_array( $_key ) ) :
                                    $tax_title = $xml_data_values['values'][$_key[0]]['value'];
                                    $new_form_data[ $form_key ][] = array(
                                        'taxonomy'      =>  $tax_value['taxonomy'],
                                        'title'         =>  $tax_title,
                                    );
                                endif;
                            endif;
                        endif;
                    endforeach;
                elseif ( $form_key === 'acf' && is_array( $form_value ) ) :
                    $j = 0;
                    foreach ( $form_value as $acf_key => $acf_value ) :
                        if ( $acf_value['value'] !== '0' && $acf_value['field'] !== '0' ) :
                            $j++;

                            $title_explode = explode( '/@', $acf_value['value'] );
                            if ( count ( $title_explode ) > 1 ) :
                                $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0] , $xml_data_values['values']) ;

                                $_field = json_decode( wp_unslash( $acf_value['field'] ), true );

                                if ( is_array( $_key ) ) :
                                    $acf_title = $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]];
                                    $acf_form_data[ $form_key ][] = array(
                                        'field'         =>  $_field['name'],
                                        'key'           =>  $_field['id'],
                                        'type'          =>  $_field['type'],
                                        'value'         =>  $acf_title,
                                    );
                                endif;
                            else :
                                $_key =  Moove_Importer_Controller::moove_recursive_array_search( $acf_value['value'] , $xml_data_values['values']) ;
                                $_field = json_decode( wp_unslash( $acf_value['field'] ), true );

                                if ( is_array( $_key ) ) :
                                    $acf_title = $xml_data_values['values'][$_key[0]]['value'];
                                    $acf_form_data[ $form_key ][] = array(
                                        'field'         =>  $_field['name'],
                                        'key'           =>  $_field['id'],
                                        'type'          =>  $_field['type'],
                                        'value'         =>  $acf_title,
                                    );
                                endif;
                            endif;

                        endif;
                    endforeach;
                elseif ( $form_key === 'customfields' ) :
                    $j = 0;
                    foreach ( $form_value as $cf_key => $customfields ) :
                        if ( $customfields['value'] !== '0' && $customfields['field'] !== '0' ) :
                            $j++;

                            $title_explode = explode( '/@', $customfields['value'] );
                            if ( count ( $title_explode ) > 1 ) :

                                $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0], $xml_data_values['values']) ;
                                $_field = json_decode( wp_unslash( $customfields['field'] ), true );
                                if ( is_array( $_key ) ) :
                                    $customfields_title = $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]];
                                    $customfields_data[ $form_key ][] = array(
                                        'field'         =>  wp_unslash( $customfields['field'] ),
                                        'value'         =>  $customfields_title,
                                    );
                                endif;

                            else :
                                $_key =  Moove_Importer_Controller::moove_recursive_array_search( $customfields['value'], $xml_data_values['values']) ;
                                $_field = json_decode( wp_unslash( $customfields['field'] ), true );
                                if ( is_array( $_key ) ) :
                                    $customfields_title = $xml_data_values['values'][$_key[0]]['value'];
                                    $customfields_data[ $form_key ][] = array(
                                        'field'         =>  wp_unslash( $customfields['field'] ),
                                        'value'         =>  $customfields_title,
                                    );
                                endif;
                            endif;

                        endif;
                    endforeach;
                elseif ( $form_key === 'post_date' ) :

                    $title_explode = explode( '/@', $form_value );
                    if ( count ( $title_explode ) > 1 ) :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0] , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :

                        try {
                            $new_date = date( 'Y-m-d H:i:s', strtotime( $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]] ) );
                            $new_form_data[ $form_key ] = $new_date;
                        } catch (Exception $e) {
                            $new_form_data[ $form_key ] = date( 'Y-m-d H:i:s', strtotime() );
                        }

                    endif;

                    else :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $form_value , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :

                            try {
                                $new_date = date( 'Y-m-d H:i:s', strtotime( $xml_data_values['values'][$_key[0]]['value'] ) );
                                $new_form_data[ $form_key ] = $new_date;
                            } catch (Exception $e) {
                                $new_form_data[ $form_key ] = date( 'Y-m-d H:i:s', strtotime() );
                            }

                        endif;
                    endif;


                elseif ( $form_key === 'post_content' || $form_key === 'post_excerpt' ) :

                    $title_explode = explode( '/@', $form_value );
                    if ( count ( $title_explode ) > 1 ) :                        
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0] , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :
                            $value =  $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]];
                            $value = apply_filters( 'moove_importer_sanitize_value', htmlspecialchars_decode ( $value ) );
                            $new_form_data[ $form_key ] = apply_filters('the_content', htmlspecialchars_decode( $value ) );
                        endif;

                    else :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $form_value , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :
                            $value =  $xml_data_values['values'][$_key[0]]['value'];
                            $value = apply_filters( 'moove_importer_sanitize_value', htmlspecialchars_decode ( $value ) );
                            $new_form_data[ $form_key ] = apply_filters('the_content', htmlspecialchars_decode( $value ) );
                        endif;
                    endif;

                else :

                    $title_explode = explode( '/@', $form_value );
                    if ( count ( $title_explode ) > 1 ) :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0] , $xml_data_values['values'] );            
                        if ( is_array( $_key ) ) :
                            $value =  $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]];
                            $new_form_data[ $form_key ] = $value;
                        endif;
                    else :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $form_value , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :
                            $value =  $xml_data_values['values'][$_key[0]]['value'];
                            $new_form_data[ $form_key ] = $value;

                        endif;
                    endif;


                endif;
            else :
                if ( $form_key === 'post_status' || $form_key === 'post_type' ) :

                    $new_form_data[ $form_key ] = $form_value;

                elseif ( $form_key === 'post_author' ) :

                    $new_form_data[ $form_key ] = intval( $form_value );

                elseif ( $form_key === 'post_featured_image' ) :

                    $title_explode = explode( '/@', $form_value );
                    if ( count ( $title_explode ) > 1 ) :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $title_explode[0] , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :
                            $img_url = $xml_data_values['values'][$_key[0]]['attributes']['@attributes'][$title_explode[1]];
                            $new_form_data[ $form_key ] = preg_replace( '/\\?.*/', '', $img_url );
                        endif;
                    else :
                        $_key =  Moove_Importer_Controller::moove_recursive_array_search( $form_value , $xml_data_values['values'] );
                        if ( is_array( $_key ) ) :
                            $img_url = $xml_data_values['values'][$_key[0]]['value'];
                            $new_form_data[ $form_key ] = preg_replace( '/\\?.*/', '', $img_url );
                        endif;

                    endif;

                endif;
            endif;
        endforeach;
        // Create post object.


        $new_post = array();
        foreach ( $new_form_data as $form_key => $form_value ) :
            if ( $form_key === 'post_title' ) :
                $new_post[ $form_key ] = strip_tags( htmlspecialchars_decode( $form_value ) );

            else :
                $new_post[ $form_key ] = $form_value;
            endif;
        endforeach;
        // Insert the post into the database.
        $post_id = wp_insert_post( $new_post );
        if ( isset( $new_form_data['taxonomies'] ) && is_array( $new_form_data['taxonomies'] ) ) :
            foreach ( $new_form_data['taxonomies'] as $taxonomy_value ) :
                if ( $taxonomy_value['title'] !== "0" ) :
                    $taxonomy   = $taxonomy_value['taxonomy'];
                    $title = preg_replace( '/\s+/', '', $taxonomy_value['title'] );
                    $values = array_filter( explode( ',', $title ) );
                    wp_set_object_terms( $post_id, $values, $taxonomy, false );
                endif;
            endforeach;
        endif;
        if ( isset( $new_form_data[ 'post_featured_image' ] ) ) :
            Moove_Importer_Controller::moove_set_featured_image( $post_id, $new_form_data[ 'post_featured_image' ], true );
        endif;
        if ( $acf_form_data ) :
            Moove_Importer_Controller::moove_instert_acf_fields( $acf_form_data, $post_id );
        endif;
        if ( $customfields_data ) :
            Moove_Importer_Controller::moove_instert_custom_fields( $customfields_data, $post_id );
        endif;
        return ( $post_id ) ? true : false;
    }
}
$moove_importer_controller = new Moove_Importer_Controller();
