<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists('UM_WP_User_Frontend')){

    class UM_WP_User_Frontend {

        static protected $instance = null;

        public $classes = [];


        public static function autoload( $class_name ){

            $class_name = str_replace( 'umwpuf','includes', $class_name );
            $class_name = str_replace( '\\','/', $class_name );
            $array = explode( '/', strtolower( $class_name ) );
            $class_file_name = 'class-'. end( $array ).'.php';
            $array[ count( $array ) - 1 ] = strtolower($class_file_name);
            $class_name = implode('/',$array);

            if( file_exists( UMWPUF_PATH.$class_name ) ){
                require UMWPUF_PATH.$class_name;
            }

        }


        public static function instance(){

            if( is_null( self::$instance ) ){
                self::$instance = new self();
            }

            return self::$instance;

        }


        function __construct(){
            $this->includes();
        }


        function includes(){

            $this->helper();
            $this->profile();

	        $this->admin();

        }




        function helper(){
            if( empty($this->classes['helper'])){
                $this->classes['helper'] = new umwpuf\Helper();
            }

            return $this->classes['helper'];
        }




        function profile(){
            if( empty($this->classes['profile'])){
                $this->classes['profile'] = new umwpuf\Profile();
            }

            return $this->classes['profile'];
        }





        function admin(){
            if( empty($this->classes['admin'])){
                $this->classes['admin'] = new umwpuf\admin\Admin();
            }

            return $this->classes['admin'];
        }

        


        function get_template_part( $template , $args = [] ){


            if( ! empty( $args ) ){

                extract( $args );
            }

            $path = trailingslashit( get_stylesheet_directory() ).'um-wp-user-frontend/'.$template.'.php';

            if( ! file_exists($path)){

                $path = UMWPUF_PATH.'templates/'.$template.'.php';

            }

            $path = apply_filters( 'UMWPUF_template',$path, $template );

            include $path;

        }




    }

}

spl_autoload_register( array( 'UM_WP_User_Frontend','autoload' ) );

if( ! function_exists('UMWPUF')){

    function UMWPUF(){
        return UM_WP_User_Frontend::instance();
    }

}

UMWPUF();