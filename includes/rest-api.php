<?php
/**
 * Rest API example class
 */

/**
 * Class DT_Manychat_Endpoints
 */
class DT_Manychat_Endpoints
{
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }


    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'dt-public/v1';

        register_rest_route(
            $namespace, '/manychat', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'create_contact' ],
                ],
            ]
        );
    }


    public function create_contact( WP_REST_Request $request ) {
        return 111;
        if ( !$this->has_permission() ){
            return new WP_Error( "private_endpoint", "Missing Permissions", [ 'status' => 400 ] );
        }

        // run your function here

        dt_write_log('success ' . __METHOD__ );

        return true;
    }
}

/**
 * Site Link Types
 */
add_filter( 'site_link_type', 'manychat_link_type', 10, 1 );
add_filter( 'site_link_type_capabilities', 'manychat_type_capabilities', 10, 1 );

function manychat_link_type( $types ){
    if ( !isset( $types["manychat"] ) ){
        $types["manychat"] = "ManyChat";
    }
    return $types;
}
function manychat_type_capabilities( $args ){
    if ( $args['connection_type'] === "manychat" ){
        $args['capabilities'][] = 'create_contact';
        $args['capabilities'][] = 'update_contact';
    }
    return $args;
}
