<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class NF_Action_Email
 */
final class Superaddons_Actions_PDF extends NF_Abstracts_Action
{
    protected $_name = 'pdf_creator';
    protected $_timing = 'normal'; // for API calls
    protected $_priority = '10';
    protected $list_pdfs = array();
    public function __construct()
    {
        global $wpdb;
        parent::__construct();
        $options=array();
        $options[]= array("label"=>esc_html__("Chooose template","pdf-for-ninja-forms"),"value"=>0);
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
        $this->_nicename = esc_html__( 'PDF', 'text-domain' );
        $$form_id = $_GET["form_id"];
        //$emails = Ninja_Forms()->form( $form_id )->get_action("email")->get_setting();
        $settings = array(
            'pdf_template' => array(
                'name' => 'pdf_template',
                'type' => 'select',
                'group' => 'primary',
                'label' => esc_html__( 'PDF Template', 'pdf-for-ninja-forms' ),
                'placeholder' => '',
                'options' => $options,
                'width' => 'full',
                'value' => '',
                'use_merge_tags' => array(
                    'include' => array(
                        'calcs',
                    ),
                ),
            ),
            'pdf_template_atta' => array(
                'name' => 'pdf_template_atta',
                'type' => 'select',
                'label' => esc_html__( 'Product Type', 'ninja-forms' ),
                'width' => 'full',
                'group' => '',
                'options' => array(
                    array(
                        'label' => esc_html__( 'Single Product (default)', 'ninja-forms' ),
                        'value' => 'single'
                    ),
                    array(
                        'label' => esc_html__( 'Multi Product - Dropdown', 'ninja-forms' ),
                        'value' => 'dropdown'
                    ),
                    array(
                        'label' => esc_html__( 'Multi Product - Choose Many', 'ninja-forms' ),
                        'value' => 'checkboxes'
                    ),
                    array(
                        'label' => esc_html__( 'Multi Product - Choose One', 'ninja-forms' ),
                        'value' => 'radiolist'
                    ),
                    array(
                        'label' => esc_html__( 'User Entry', 'ninja-forms' ),
                        'value' => 'user'
                    ),
                    array(
                        'label' => esc_html__( 'Hidden', 'ninja-forms' ),
                        'value' => 'hidden'
                    ),
                ),
                'value' => 'single',
                'use_merge_tags' => FALSE
            ),
            'pdf_template_name' => array(
                'name' => 'pdf_template_name',
                'type' => 'textbox',
                'group' => 'primary',
                'label' => esc_html__( 'PDF Template Custom Name', 'ninja-forms' ),
                'value' => '',
                'placeholder' => '{number_key}-name.pdf',
                'width' => 'one-half',
                'use_merge_tags' => TRUE,
            ),
            'pdf_template_password' => array(
                'name' => 'pdf_template_password',
                'type' => 'textbox',
                'group' => 'primary',
                'label' => esc_html__( 'PDF Password', 'ninja-forms' ),
                'placeholder' => ' Password protect the PDF if security is a concern',
                'value' => '',
                'width' => 'one-half',
                'use_merge_tags' => TRUE,
            ),
        );
        $this->_settings = array_merge( $this->_settings, $settings );
    }
    public function save( $action_settings ){
    }
    public function process( $action_settings, $form_id, $data ) {
        global $ninja_forms_processing;
        if( isset($action_settings["pdf_template"]) && $action_settings["pdf_template"] > 0){
            //var_dump($action_settings["id"]);
        }
        return $data;
    }
}
