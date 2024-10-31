<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Superaddons_Pdf_Creator_Ninja_Forms_Backend {
	function __construct(){
		add_filter("yeepdf_shortcodes",array($this,"add_shortcode"));
		add_action("yeepdf_head_settings",array($this,"add_head_settings"));
		add_action( 'save_post_yeepdf',array( $this, 'save_metabox' ), 10, 2 );;
        add_filter('ninja_forms_action_email_settings', array($this,"add_settings"), 10, 1);
        add_filter("ninja_forms_action_email_attachments",array($this,"notification_attachments"),10,3);
        //add_filter( 'ninja_forms_register_actions', array($this,"register_actions") );
		add_filter("superaddons_pdf_check_pro",array($this,"check_pro"));
	}
	function register_actions( $actions ) {
		$actions['pdf_creator'] = new Superaddons_Actions_PDF();
		return $actions;
	}
	function check_pro($pro){
		$check = get_option( '_redmuber_item_1681');
		if($check == "ok"){
			$pro = true;
		}
		return $pro;
	}
	function add_head_settings($post){
		global $wpdb;
		$post_id= $post->ID;
		$data = get_post_meta( $post_id,'_pdfcreator_ninja_form',true);
		?>
		<div class="yeepdf-testting-order">
			<select name="pdfcreator_ninja_form" class="builder_pdf_woo_testing">
			<option value='-1'>--- <?php esc_html_e("Ninja Forms","pdf-for-wpforms") ?> ---</option>
				<?php
				$table_name = $wpdb->prefix . 'nf3_forms';
					$templates = $wpdb->get_results( 
					"
						SELECT id,title 
						FROM $table_name
					"
				);
				if( count($templates) > 0 ) {
					foreach ( $templates as $template ) {
						$form_id = $template->id;
						$form_title = $template->title;
						?>
							<option <?php selected($data,$form_id) ?> value="<?php echo esc_attr($form_id) ?>"><?php echo esc_html($form_title) ?></option>
						<?php
					}
				}else{
					printf( "<option value='0'>%s</option>",esc_html__("No Form","ninja-forms-pdfcreator"));
				}
					?>
			</select>
		</div>
		<?php
    }
    function save_metabox($post_id, $post){
        if( isset($_POST['pdfcreator_ninja_form'])) {
            $id = sanitize_text_field($_POST['pdfcreator_ninja_form']);
            update_post_meta($post_id,'_pdfcreator_ninja_form',$id);
        }
    }
	function add_shortcode($shortcode) {
		global $wpdb;
		if( isset($_GET["post"]) ){
			$table_name = $wpdb->prefix . 'nf3_forms';
			$post_id = sanitize_text_field($_GET["post"]);
			$form_id = get_post_meta( $post_id,'_pdfcreator_ninja_form',true);
			if($form_id && $form_id > 0){
				$inner_shortcode = array(
					"{form:title}" => "Form Name",
					"{form:id}"=>"Form ID",
					"{all_fields_table}"=>"All Fields Table",
				);
				$fields_list = Ninja_Forms()->form( $form_id )->get_fields();
				$hidden_field_types = apply_filters( 'nf_sub_hidden_field_types', array() );
				foreach( $fields_list as $field ){
					$field_id = $field->get_id();
					$label = $field->get_setting( 'label' );
					$name = $field->get_setting( 'key' );
					if (!is_int($field_id)) continue;
					if( in_array( $field->get_setting( 'type' ), $hidden_field_types ) ) continue;
					$inner_shortcode["{field:".$name."}"] = $label;
				}
				$shortcode["Ninja Forms"] = $inner_shortcode;    
			}
		}
		return $shortcode;
	}
	function add_settings($settings){
		global $wpdb;
		$options=array();
		$options[]= array("label"=>esc_html__("Chooose template","ninja-forms-pdfcreator"),"value"=>0);
		$yeepdfs = get_posts(array( 'post_type' => 'yeepdf','post_status' => 'publish','numberposts'=>-1 ) );
		$templates = array();
		if($yeepdfs){
			foreach ( $yeepdfs as $post ) {
				$post_id = $post->ID;
                $options[]= array("label"=>esc_html($post->post_title),"value"=>esc_attr($post_id));
			}
		}else{
            $options[]= array("label"=>__("No PDF template","pdf-for-wpforms"),"value"=>-1);
		}
		$settings['pdf_template'] = array(
            'name'        => 'pdf_template',
            'type'        => 'select',
            'options' => $options,
            'label'       => __('PDF Template', "ninja-forms-pdfcreator"),
            'group'       => 'primary',
            'width'          => 'full',
        );
		$settings['pdf_template_name'] = array(
            'name'        => 'pdf_template_name',
            'type'        => 'textbox',
			'default' 	  => '',
            'placeholder' => 'rand-{name}.pdf',
            'label'       => __('PDF Name', "ninja-forms-pdfcreator"),
            'group'       => 'primary',
            'use_merge_tags' => true,
            'width'          => 'full',
        );
        $settings['pdf_template_name_number'] = array(
            'name'        => 'pdf_template_name_number',
            'type'        => 'toggle',
            'placeholder' => '{number}name.pdf',
            'label'       => __('Show name key number ( name-{number_key}.pdf )', "ninja-forms-pdfcreator"),
            'group'       => 'primary',
            'width'          => 'full',
        );
        $settings['pdf_template_password'] = array(
            'name'        => 'pdf_template_password',
            'type'        => 'textbox',
			'default' 	  => '',
            'placeholder' => '',
            'label'       => __('PDF Password', "ninja-forms-pdfcreator"),
            'group'       => 'primary',
            'use_merge_tags' => true,
            'width'          => 'full',
        );
        return $settings;
	}
	function notification_attachments(  $attachments, $data, $settings ) {
		$datas = array();
		$id_link_pdf = "";
		foreach( $data["fields_by_key"] as $k=> $d ){
			$datas["{field:".$k."}"] = $d["value"];
			if($d["value"] == "[pdf]"){
				$id_link_pdf = $d["id"];
			}
		}
        if (isset($settings['pdf_template']) &&  $settings['pdf_template'] > 0) {
			$name = $settings["pdf_template_name"];
			$template_id = $settings["pdf_template"];
			$pdf_template_name_number = $settings["pdf_template_name_number"];
			$password = $settings["pdf_template_password"];
			if( $name == ""){
				$name= "contact-form";
			}else{
				$name = apply_filters( 'ninja_forms_merge_tags', $name);
			}
			if( $password != ""){
				$password = apply_filters( 'ninja_forms_merge_tags', $password);
			}
			if( $pdf_template_name_number == 1 ){
				$name = sanitize_title($name)."-".rand(1000,9999);
			}else{
				$name = sanitize_title($name);
			}
			$data_send_settings = array(
					"id_template"=> $template_id,
					"type"=> "html",
					"name"=> $name,
					"datas" =>$datas,
					"return_html" =>true,
				);
			$message =Yeepdf_Create_PDF::pdf_creator_preview($data_send_settings);
			$message = apply_filters( 'ninja_forms_merge_tags', $message);
			$data_send_settings_download = array(
					"id_template"=> $template_id,
					"type"=> "upload",
					"name"=> $name,
					"datas" =>$datas,
					"html" =>$message,
					"password" =>$password,
				);
			$data_send_settings_download = apply_filters("pdf_before_render_datas",$data_send_settings_download);
			$path =Yeepdf_Create_PDF::pdf_creator_preview($data_send_settings_download);
			if( $id_link_pdf != ""){
				$upload_dir = wp_upload_dir();
				$submission_id = $data["actions"]["save"]["sub_id"];
				$links = get_post_meta($submission_id,"_field_".$id_link_pdf,true);
				$links = str_replace("[pdf]", "", $links);
				$link_path = $upload_dir["baseurl"]."/pdfs/".$name.".pdf";
				if($links != ""){
					$links = explode(" | ", $links);
					$links[]= $link_path;
					$links = implode(" | ", $links);
				}else{
					$links = $link_path;
				}
				update_post_meta($submission_id,"_field_".$id_link_pdf,$links);
			}
			$attachments[] = $path;
        }
        return $attachments;
	}
}
new Superaddons_Pdf_Creator_Ninja_Forms_Backend;