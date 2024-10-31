<?php
/**
 * Plugin Name: PDF Creator For Ninja Forms + Drag And Drop Template Builder
 * Description: Ninja Forms PDF Customizer is a helpful tool that helps you build and customize the PDF Templates for Ninja Forms.
 * Plugin URI: https://add-ons.org/plugin/ninja-forms-pdf-customizer/
 * Version: 3.6.6
 * Requires Plugins: ninja-forms
 * Requires PHP: 5.6
 * Author: add-ons.org
 * Author URI: https://add-ons.org/
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define( 'BUIDER_PDF_NJ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BUIDER_PDF_NJ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
if(!class_exists('Yeepdf_Creator_Builder')) {
    require 'vendor/autoload.php';
    if(!defined('YEEPDF_CREATOR_BUILDER_PATH')) {
        define( 'YEEPDF_CREATOR_BUILDER_PATH', plugin_dir_path( __FILE__ ) );
    }
    if(!defined('YEEPDF_CREATOR_BUILDER_URL')) {
        define( 'YEEPDF_CREATOR_BUILDER_URL', plugin_dir_url( __FILE__ ) );
    }
    class Yeepdf_Creator_Builder {
        function __construct(){
            $dir = new RecursiveDirectoryIterator(YEEPDF_CREATOR_BUILDER_PATH."backend");
            $ite = new RecursiveIteratorIterator($dir);
            $files = new RegexIterator($ite, "/\.php/", RegexIterator::MATCH);
            foreach ($files as $file) {
                if (!$file->isDir()){
                    require_once $file->getPathname();
                }
            }
            include_once YEEPDF_CREATOR_BUILDER_PATH."libs/phpqrcode.php";
            include_once YEEPDF_CREATOR_BUILDER_PATH."frontend/index.php";
        }
    }
    new Yeepdf_Creator_Builder;
}
class Yeepdf_Creator_Ninja_Forms_Builder { 
    function __construct(){
        //include BUIDER_PDF_NJ_PLUGIN_PATH."ninja-forms/actions.php";
        include BUIDER_PDF_NJ_PLUGIN_PATH."ninja-forms/index.php";
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'add_link') );
        register_activation_hook( __FILE__, array($this,'activation') );
        include BUIDER_PDF_NJ_PLUGIN_PATH."superaddons/check_purchase_code.php";
        new Superaddons_Check_Purchase_Code( 
            array(
                "plugin" => "pdf-for-ninja-forms/pdf-for-ninja-forms.php",
                "id"=>"1681",
                "pro"=>"https://add-ons.org/plugin/ninja-forms-pdf-customizer",
                "plugin_name"=> "PDF for Ninja Forms",
                "document"=>"https://pdf.add-ons.org/ninja-forms/"
            )
        );
    }
    function add_link( $actions ) {
        $actions[] = '<a target="_blank" href="https://pdf.add-ons.org/document/" target="_blank">'.esc_html__( "Document", "pdf-for-contact-form-7" ).'</a>';
        $actions[] = '<a target="_blank" href="https://add-ons.org/supports/" target="_blank">'.esc_html__( "Supports", "pdf-for-contact-form-7" ).'</a>';
        return $actions;
    }
    function activation() {
        $check = get_option( "yeepdf_ninja_forms_setup" );
        if( !$check ){           
            $data = file_get_contents(BUIDER_PDF_NJ_PLUGIN_PATH."ninja-forms/form-import.json");
            $my_template = array(
            'post_title'    => "Ninja Form Default PDF",
            'post_content'  => "",
            'post_status'   => 'publish',
            'post_type'     => 'yeepdf'
            );
            $id_template = wp_insert_post( $my_template );
            add_post_meta($id_template,"data_email",$data);      
            add_post_meta($id_template,"_builder_pdf_settings_font_family",'dejavu sans');
            update_option( "yeepdf_ninja_forms_setup",$id_template );     
        } 
    }
}
new Yeepdf_Creator_Ninja_Forms_Builder;
if(!class_exists('Superaddons_List_Addons')) {  
    include BUIDER_PDF_NJ_PLUGIN_PATH."add-ons.php"; 
}