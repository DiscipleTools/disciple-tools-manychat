<?php
/**
 * Rest API example class
 */

/**
 * Class DT_Manychat_Endpoints
 */
class DT_Manychat_Endpoints
{
    public $permissions = array( 'create_contacts', 'update_any_contacts' );

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
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
            $namespace, '/manychat', array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'route_request' ),
                ),
            )
        );
    }

    public function route_request( WP_REST_Request $request ) {

        $params = $request->get_params();
        $headers = $request->get_headers();
        $current_ip = Site_Link_System::get_real_ip_address();
        $fails = get_transient( 'manychat_fails' );

        // fails honeypot
        if ( ! empty( $fails ) ) {
            if ( isset( $fails['ip'] ) && $fails['ip'] === $current_ip ) {
                if ( $fails['ip'] > 10 ) {
                    return new WP_Error( __METHOD__, "Too many attempts", array( 'status' => 400 ) );
                }
            }
        }

        // test user agent
        if ( ( $headers['user_agent'][0] ?? false ) !== 'ManyChat' ) {
            return new WP_Error( __METHOD__, "Not ManChat user agent", array( 'status' => 400 ) );
        }

        // test token
        $post_ids = Site_Link_System::get_list_of_sites_by_type( array( 'manychat' ), 'post_ids' );
        if ( empty( $post_ids ) ) {
            return new WP_Error( __METHOD__, "No manychat links setup", array( 'status' => 400 ) );
        }
        $token_status = false;
        foreach ( $post_ids as $post_id ) {
            $token = get_post_meta( $post_id, 'token', true );
            if ( $token === $headers['token'][0] ?? false ) {
                $token_status = true;
            }
        }
        if ( ! $token_status ) {
            $fails = get_transient( 'manychat_fails' );
            if ( ! isset( $fails['ip'] ) ) {
                $fails['ip'] = 0;
            }
            $fails['ip'] = $fails['ip']++;
            set_transient( 'manychat_fails', $fails, 6000 );
            return new WP_Error( __METHOD__, "Mismatch api token", array( 'status' => 400 ) );
        }

        switch ( $headers['action'][0] ) {
            case 'comment':
                return $this->comment( $params );
                break;
            case 'create':
                return $this->create_contact( $params );
                break;
            default:
                return new WP_Error( __METHOD__, "Mismatch api token", array( 'status' => 400 ) );
                break;
        }

    }

    public function create_contact( $params ) {

        // build create record
        $check_permission = false;
        $fields = array();
        $notes = array();

        // sanitize vars
        $name = sanitize_text_field( wp_unslash( $params['name'] ) );
        $first_name = sanitize_text_field( wp_unslash( $params['first_name'] ) );
        $key = sanitize_text_field( wp_unslash( $params['key'] ) );
        $phone = sanitize_text_field( wp_unslash( $params['phone'] ) );
        $email = sanitize_text_field( wp_unslash( $params['email'] ) );
        $live_chat_url = sanitize_text_field( wp_unslash( $params['live_chat_url'] ) );

        // build fields
        $fields['title'] = $name ?? $first_name ?? $key ?? 'No ID Supplied By ManyChat';
        $fields['sources'] = array(
            "values" => array(
                array( "value" => "manychat" )
            )
        );
        if ( isset( $phone ) && ! empty( $phone ) ) {
            $fields['contact_phone'] = array(
                "values" => array(
                    array( "value" => $phone )
                )
            );
        }
        if ( isset( $email ) && ! empty( $email ) ) {
            $fields['contact_email'] = array(
                "values" => array(
                    array( "value" => $email )
                )
            );
        }
        $notes['chat_url'] = $live_chat_url ?? '';
        $fields['notes'] = $notes;

        $result = Disciple_Tools_Contacts::create_contact( $fields, $check_permission );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_to_insert_contact', $result->get_error_message() );
        }

        // additional metafields
        update_post_meta( $result, 'manychat_post_data', $params );
        update_post_meta( $result, 'manychat_live_chat', $live_chat_url );


        return array(
            "version" => "v2",
            "status" => 'success',
            "post_id" => $result
        );
    }

    public function comment( $params ) {

        $contact_id = sanitize_text_field( wp_unslash( $params['post_id'] ) );
        $comment_html = sanitize_text_field( wp_unslash( $params['message'] ) );
        $skip_notification = (bool) $params['skip_notification'] ?? false;

        $result = Disciple_Tools_Contacts::add_comment( $contact_id, $comment_html, "comment", array(), false, $skip_notification );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_to_insert_comment', $result->get_error_message() );
        }

        return array(
            "version" => "v2",
            "status" => 'success',
        );
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
        $args['capabilities'][] = 'update_any_contact';
    }
    return $args;
}
