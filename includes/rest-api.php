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

    public function route_request( WP_REST_Request $request ) {

        $params = $request->get_params();
        $headers = $request->get_headers();
        $current_ip = Site_Link_System::get_real_ip_address();

        set_transient('manychat', [ $headers, $params ], 6000 ); // testing

        // fails honeypot
        if ( ! empty( $fails = get_transient('manychat_fails') ) ) {
            if ( isset( $fails['ip'] ) && $fails['ip'] === $current_ip ) {
                if ( $fails['ip'] > 10 ) {
                    return new WP_Error( __METHOD__, "Too many attempts", [ 'status' => 400 ] );
                }
            }
        }

        // test user agent
        if ( ( $headers['user_agent'][0] ?? false ) !== 'ManyChat' ) {
            return new WP_Error( __METHOD__, "Not ManChat user agent", [ 'status' => 400 ] );
        }

        // test token
        $post_ids = Site_Link_System::get_list_of_sites_by_type( [ 'manychat' ], 'post_ids' );
        if ( empty( $post_ids ) ) {
            return new WP_Error( __METHOD__, "No manychat links setup", [ 'status' => 400 ] );
        }
        $token_status = false;
        foreach ( $post_ids as $post_id ) {
            $token = get_post_meta( $post_id, 'token', true );
            if ( $token === $headers['token'][0] ?? false ) {
                $token_status = true;
            }
        }
        if ( ! $token_status ) {
            $fails = get_transient('manychat_fails');
            if ( ! isset( $fails['ip'] ) ) {
                $fails['ip'] = 0;
            }
            $fails['ip'] = $fails['ip']++;
            set_transient('manychat_fails', $fails, 6000 );
            return new WP_Error( __METHOD__, "Mismatch api token", [ 'status' => 400 ] );
        }

        switch ( $headers['action'][0] ) {
            case 'create':
                return $this->create_contact( $params );
                break;
            case 'update':
                break;
            default:
                break;
        }


    }

    public function create_contact( $params ) {

        // build create record
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

        return [
            "post_id" => $result
        ];
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
