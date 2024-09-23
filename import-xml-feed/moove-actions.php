<?php
/**
 * Moove_Importer_Actions File Doc Comment
 *
 * @category  Moove_Importer_Actions
 * @package   moove-feed-importer
 * @author    Gaspar Nemes
 */

/**
 * Moove_Importer_Actions Class Doc Comment
 *
 * @category Class
 * @package  Moove_Importer_Actions
 * @author   Gaspar Nemes
 */
class Moove_Importer_Actions {
	/**
	 * Global cariable used in localization
	 *
	 * @var array
	 */
	var $importer_loc_data;
	/**
	 * Construct
	 */
	function __construct() {
		$this->moove_register_scripts();
		$this->moove_register_ajax_actions();
		if ( ! defined( 'MOOVE_XML_ADDON_VERSION' ) ) :
	    add_action( 'moove_importer_sanitize_xml', array( &$this, 'moove_importer_sanitize_xml' ), 10, 1 );
	    add_action( 'moove_importer_get_attribues', array( &$this, 'moove_importer_get_attribues' ), 10, 1 );
	    add_action( 'moove_importer_check_other_taxonomies', array( &$this, 'moove_importer_check_taxonomies' ), 10, 3 );
	  endif;
	}
	/**
	 * Register Front-end / Back-end scripts
	 *
	 * @return void
	 */
	function moove_register_scripts() {
		if ( is_admin() ) :
			add_action( 'admin_enqueue_scripts', array( &$this, 'moove_importer_admin_scripts' ) );
		endif;
	}

	/**
	 * Registe BACK-END Javascripts and Styles
	 *
	 * @return void
	 */
	public function moove_importer_admin_scripts() {
		wp_enqueue_script( 'moove_importer_backend', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/js/moove_importer_backend.js', array( 'jquery' ), strtotime('now'), true );
		wp_enqueue_style( 'moove_importer_backend', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/css/moove_importer_backend.css', '', MOOVE_XML_VERSION );
	}

	/**
	 * AJAX action used by importer plugin
	 *
	 * @return void
	 */
	public function moove_register_ajax_actions() {
		add_action( 'wp_ajax_moove_read_xml', array( &$this, 'moove_read_xml' ) );

		add_action( 'wp_ajax_moove_create_post', array( &$this, 'moove_create_post' ) );

		add_action( 'wp_ajax_moove_save_import_template', array( &$this, 'moove_save_import_template' ) );

    add_action( 'wp_ajax_moove_load_import_template', array( &$this, 'moove_load_import_template' ) );

    add_action( 'wp_ajax_moove_delete_import_template', array( &$this, 'moove_delete_import_template' ) );
	}

	/**
	 * Read XML function
	 *
	 * @return void
	 */
	public function moove_read_xml() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $nonce && wp_verify_nonce( $nonce, 'moove_xml_admin_nonce_field' ) && current_user_can( 'edit_posts' ) ) :
			$args = array(
				'data' 			=> esc_sql( wp_unslash( $_POST['data'] ) ),
				'xmlaction'	=> sanitize_text_field( wp_unslash( $_POST['xmlaction'] ) ),
				'type'			=> sanitize_text_field( wp_unslash( $_POST['type'] ) ),
				'node'			=> sanitize_text_field( wp_unslash( $_POST['node'] ) ),
			);
			$move_importer = new Moove_Importer_Controller;
			$read_xml = $move_importer->moove_read_xml( $args );
			echo $read_xml;
		else :
			echo json_encode( array() );
		endif;
		die();
	}
	/**
	 * Create post function
	 *
	 * @return void
	 */
	public function moove_create_post() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $nonce && wp_verify_nonce( $nonce, 'moove_xml_admin_nonce_field' ) && current_user_can( 'edit_posts' ) ) :
			$args = array(
				'key'					=> sanitize_text_field( esc_sql( $_POST['key'] ) ),
				'value'				=> wp_unslash( $_POST['value'] ),
				'form_data'		=> esc_sql( wp_unslash( $_POST['form_data'] ) ),
			);
			$move_create_post = new Moove_Importer_Controller;
			$create_post = $move_create_post->moove_create_post( $args );
			echo $create_post;
		endif;
		die();
	}

	function moove_importer_check_taxonomies( $taxonomies, $post_types, $acf_groups ) {

		$cf = moove_importer_generate_meta_keys( $post_types['post_type'] );

		ob_start();
		if ( is_array( $cf ) && ! empty( $cf ) ) : 
			?>
			<div class="moove_cpt_tax_<?php echo $post_types['post_type']; ?>_customfields moove-importer-accordion moove_cpt_tax">
				<div class="moove-importer-accordion-header">
					<a href="#">
						<?php _e( 'Custom Fields' , 'moove'); ?>
					</a>
				</div>
				<!--  .moove-importer-dropdown-header -->
				<div class="moove-importer-accordion-content" style="display:none">
					<div class="moove-importer-dynamic-accordion">
						<div class="moove-importer-customfield-rule-holder">
							<div class="moove-importer-taxonomy-box moove-customfield-existing moove-importer-customfield-box moove-hidden moove-initial-box-existing">
								<p class="moove-importer-tax-title"><?php _e('Existing custom field box','moove'); ?><span class="moove_importer_remove_customfield_group moove_importer_remove_acf_group">remove</span></p>
								<hr>
								<p><?php _e( 'Select the custom field' , 'moove' ); ?>: </p>
								<?php if ( is_array( $cf ) && ! empty( $cf ) ) : ?>
									<select name="moove-importer-customgield-field" class="moove-customfield-dynamic-field moove-importer-customfield-type-select">
										<option value='0'>Select a field</option>
										<?php foreach ( $cf as $meta_value ) : ?>
											<option value='<?php echo $meta_value; ?>'><?php echo $meta_value; ?></option>
										<?php  endforeach; ?>
									</select>
								<?php endif; ?>

								<p><?php _e( 'Select the XML field' , 'moove' ); ?>: </p>
								<select name="moove-importer-tax-title-1" class="moove-importer-dynamic-select moove-importer-taxonomy-title moove-importer-customfield-xml-select">

								</select>
								<br />
							</div>
							<!-- moove-importer-taxonomy-box -->

						</div>
						<!--  .moove-importer-acf-rule-holder -->
						<a href="#" class="button button-primary moove_importer_add_customfield_existing">Use exiting field</a>
					</div>
					<!--  .moove-importer-dynamic-accordion -->
				</div>
				<!-- moove_cpt_tax -->
			</div>
			<!--  .moove-importer-accordion -->
			<?php 
		endif;

		if ( $acf_groups ) : 
			?>
			<div class="moove_cpt_tax_<?php echo $post_types['post_type']; ?>_acf moove-importer-accordion moove_cpt_tax">
				<div class="moove-importer-accordion-header">
					<a href="#">
						<?php _e( 'Advanced Custom Fields' , 'moove'); ?>
					</a>
				</div>
				<!--  .moove-importer-dropdown-header -->
				<div class="moove-importer-accordion-content" style="display:none">
					<div class="moove-importer-dynamic-accordion">
						<div class="moove-importer-acf-rule-holder">
							<div class="moove-importer-taxonomy-box moove-importer-acf-box moove-hidden moove-initial-box">
								<p class="moove-importer-tax-title"><?php _e('ACF field box','moove'); ?><span class="moove_importer_remove_acf_group">remove</span></p>
								<hr>
								<p><?php _e( 'Select the ACF field' , 'moove' ); ?>: </p>
								<select name="moove-importer-acf-field" class="moove-acf-dynamic-field moove-importer-acf-type-select">
									<?php echo $acf_groups; ?>
								</select>

								<p><?php _e( 'Select the XML field' , 'moove' ); ?>: </p>
								<select name="moove-importer-tax-title-1" class="moove-importer-dynamic-select moove-importer-taxonomy-title moove-importer-acf-xml-select">

								</select>
								<br />
							</div>
							<!-- moove-importer-taxonomy-box -->
						</div>
						<!--  .moove-importer-acf-rule-holder -->

						<a href="#" class="button button-primary moove_importer_add_acf_rule">Add new rule</a>
					</div>
					<!--  .moove-importer-dynamic-accordion -->
				</div>
				<!-- moove_cpt_tax -->
			</div>
			<!--  .moove-importer-accordion -->
			<?php 
		endif;
		echo ob_get_clean();
	}

	function moove_importer_get_attribues( $xmlvalue ) {
		if ( $xmlvalue['attributes'] ) : 
			?>
			<span class="node-attributes">
				<strong class="node-attr-title">attributes:</strong>

				<?php foreach ( $xmlvalue['attributes'] as $attr_key => $attr_val ) : ?>
					<p>
						<strong>
							@<?php echo $attr_key; ?>:
						</strong>
						<?php echo $attr_val; ?>
					</p>
				<?php endforeach; ?>
			</span>
			<?php 
		endif;
	}

	function moove_importer_sanitize_xml( $xml ) {
		try {
			$dom_sxe = dom_import_simplexml($xml);

			$dom = new DOMDocument('1.0');
			if ( $dom && $dom_sxe ) :
				$dom_sxe = $dom->importNode($dom_sxe, true);
				$dom_sxe = $dom->appendChild($dom_sxe);

				$element = $dom->childNodes->item(0);
				foreach ($xml->getDocNamespaces(true) as $name => $uri) {
					$element->removeAttributeNS($uri, $name);
				}
				ob_start();
				echo $dom->saveXML();
				$xml_string = ob_get_clean();
				$xml = new SimpleXMLElement( $xml_string );
			endif;
			return $xml;
		} catch (Exception $e) {
			$xml = new SimpleXMLElement( $xml );
			return $xml;
		}
	}

	function moove_save_import_template() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $nonce && wp_verify_nonce( $nonce, 'moove_xml_admin_nonce_field' ) && current_user_can( 'manage_options' ) ) :
			if ( $_POST && is_array( $_POST ) ) :
				$type = sanitize_text_field( $_POST['type'] );
				$extension 	= sanitize_text_field( $_POST['extension'] );
				$allowed_extensions = apply_filters('uat_allowed_extenstions', array( 'xml', 'rss' ) );

				if ( $extension && in_array( strtolower( $extension ), $allowed_extensions ) ) :
					$filename 	= function_exists('uniqid') ? uniqid( strtotime('now') ) : 'uat_tpl_' . strtotime('now');
					if ( $type === 'upload' ) :
						$xml = wp_unslash( $_POST['file'] );
						$filename = $filename . "." . $extension;
						$target_dir = dirname( __FILE__ ) . "/uploads/" . $filename;
						file_put_contents( $target_dir, $xml, FILE_APPEND | LOCK_EX );
						$filename = plugins_url( basename( dirname( __FILE__ ) ) ) . '/uploads/' . $filename;
					else :
						$filename = sanitize_text_field( $_POST['url'] );
					endif;
					if ( isset($_POST['form_data'] ) && is_array( $_POST['form_data'] ) ) :
		                // Create post object
						$import_data = array(
							'post_title'    => wp_strip_all_tags( $_POST['name'] ),
							'post_status'   => 'publish',
							'post_type'     => 'moove_feed_importer'
						);

		        // Insert the post into the database
						$post_id = wp_insert_post( $import_data );
						if ( $post_id ) :
							foreach ( $_POST['form_data'] as $field_name => $field_value ) :
								add_post_meta( $post_id, 'import_'.  $field_name , $field_value );
							endforeach;
							add_post_meta( $post_id, 'import_xml_url', $filename );
							add_post_meta( $post_id, 'import_type', wp_strip_all_tags( $_POST['type'] ) );
							add_post_meta( $post_id, 'import_limit', wp_strip_all_tags( $_POST['limit'] ) );
							add_post_meta( $post_id, 'import_selected_node', sanitize_text_field( $_POST['selected_node'] ) );
							$slug = get_post_field( 'post_name', get_post( $post_id ) );
							$update_post = array(
								'ID'           => $post_id,
								'post_title'   => $slug
							);
							wp_update_post( $update_post );
							echo json_encode( array( 'success' => 'true', 'message' => 'Post created', 'slug' => $slug, 'template_id' => $post_id  ) );
							die();
						else :
							echo json_encode( array( 'success' => 'false', 'message' => 'Post not created!' ) );
							die();
						endif;
					else :
						echo json_encode( array( 'success' => 'false', 'message' => 'Form data empty!' ) );
						die();
					endif;
				else :
					echo json_encode( array( 'success' => 'false', 'message' => 'Unsupported extension, please check your feed!' ) );
					die();
				endif;			
			else :
				echo json_encode( array( 'success' => 'false', 'message' => 'POST not set!' ) );
				die();
			endif;
		else :
			echo json_encode( array( 'success' => 'false', 'message' => 'Check nonce!' ) );
				die();
		endif;
	}

	function moove_load_import_template() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $nonce && wp_verify_nonce( $nonce, 'moove_xml_admin_nonce_field' ) && current_user_can( 'edit_posts' ) ) :
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : false;
			if ( $template_id ) :
				$post_meta = get_metadata( 'post', $template_id, '', true);
				$template_data = array();
				if ( $post_meta && is_array( $post_meta ) ) :
					foreach ( $post_meta as $meta_key => $meta_value ) :
						$new_value = maybe_unserialize( $meta_value[0] );
						if ( $new_value && $new_value !== '0' && ! is_array( $new_value ) ) :
							$new_key = str_replace( "import_", "", $meta_key );
							$template_data[$new_key] = maybe_unserialize( $new_value );
						elseif ( is_array( $new_value ) ) :
							$template_array = array();
							foreach ( $new_value as $array_key => $array_value ) :
								if ( isset( $array_value['title'] ) && $array_value['title'] !== '0' ) :
									$template_array[ $array_key ] = $array_value;
								endif;
							endforeach;
							$new_key = str_replace( "import_", "", $meta_key );
							if ( $template_array && !empty( $template_array ) ) :
								$template_data[ $new_key ] = $template_array;
							endif;
						endif;
					endforeach;
					echo json_encode( array( 'success' => 'true', 'message' => '', 'data' => $template_data ) );
					die();
				else :
					echo json_encode( array( 'success' => 'false', 'message' => 'TEMPLATE ID not set!', 'data' => $template_data ) );
					die();
				endif;
			else :
				echo json_encode( array( 'success' => 'false', 'message' => 'TEMPLATE ID not set!' ) );
				die();
			endif;
		else :
			echo json_encode( array( 'success' => 'false', 'message' => 'Check nonce!' ) );
				die();
		endif;
	}

	function moove_delete_import_template() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $nonce && wp_verify_nonce( $nonce, 'moove_xml_admin_nonce_field' ) && current_user_can( 'manage_options' ) ) :
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : '';
			if ( $template_id ) :
				wp_delete_post( $template_id );
				echo json_encode( array( 'success' => 'true', 'message' => '' ) );
				die();
			else :
				echo json_encode( array( 'success' => 'false', 'message' => 'TEMPLATE ID not set!' ) );
				die();
			endif;
		else :
			echo json_encode( array( 'success' => 'false', 'message' => 'TEMPLATE ID not set!' ) );
			die();
		endif;
	}
}
new Moove_Importer_Actions();
