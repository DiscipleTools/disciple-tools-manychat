<?php
/**
 * Rest API example class
 */

/**
 * Class DT_Manychat_Endpoints
 */
class DT_Manychat_Endpoints
{
    public $permissions = [ 'create_contacts', 'update_any_contacts' ];

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

        $params = $request->get_params();
        set_transient('manychat', $params, '100000' );

        // @todo test for matching API key

//        if ( !$this->has_permission() ){
//            return new WP_Error( "private_endpoint", "Missing Permissions", [ 'status' => 400 ] );
//        }

        if ( empty( $params ) ) {
            return new WP_Error( __METHOD__, "Missing Params", [ 'status' => 400 ] );
        }

        $check_permission = false;
        $fields = [];
        $notes = [];

        $fields['title'] = $params['name'] ?? $params['first_name'] ?? $params['key'] ?? 'No ID Supplied By ManyChat';
        $fields['sources'] = [
            "values" => [
                [ "value" => "manychat" ]
            ]
        ];
        if ( isset( $params['phone'] ) && ! empty( $params['phone'] ) ) {
            $fields['contact_phone'] = [
                "values" => [
                    [ "value" => $params['phone'] ]
                ]
            ];
        }
        if ( isset( $params['email'] ) && ! empty( $params['email'] ) ) {
            $fields['contact_email'] = [
                "values" => [
                    [ "value" => $params['eamil'] ]
                ]
            ];
        }
        $notes['chat_url'] = $params['live_chat_url'] ?? '';
        $fields['notes'] = $notes;

        $result = Disciple_Tools_Contacts::create_contact( $fields, $check_permission );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_to_insert_contact', $result->get_error_message() );
        }

        update_post_meta( $result, 'manychat_subscribed', $params['subscribed'] );
        update_post_meta( $result, 'manychat_key', $params['key'] );
        update_post_meta( $result, 'manychat_id', $params['id'] );
        update_post_meta( $result, 'manychat_page_id', $params['page_id'] );

        // run your function here

        dt_write_log('success ' . __METHOD__ );

        return $result;
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
