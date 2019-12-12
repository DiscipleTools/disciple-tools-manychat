<?php
/**
 * DT_Manychat_Menu class for the admin page
 *
 * @class       DT_Manychat_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

/**
 * Initialize menu class
 */
DT_Manychat_Menu::instance();

/**
 * Class DT_Manychat_Menu
 */
class DT_Manychat_Menu {

    public $token = 'dt_manychat';

    private static $_instance = null;

    /**
     * DT_Manychat_Menu Instance
     *
     * Ensures only one instance of DT_Manychat_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Manychat_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( "admin_menu", array( $this, "register_menu" ) );

    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ), 'manage_dt', 'dt_extensions', [ $this, 'extensions_menu' ], 'dashicons-admin-generic', 59 );
        add_submenu_page( 'dt_extensions', __( 'ManyChat', 'dt_manychat' ), __( 'ManyChat', 'dt_manychat' ), 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php esc_attr_e( 'ManyChat', 'dt_manychat' ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', 'dt_manychat' ) : print ''; ?>"><?php esc_attr_e( 'Configuration', 'dt_manychat' ) ?></a>
                <a href="<?php echo esc_attr( $link ) . 'second' ?>" class="nav-tab <?php ( $tab == 'second' ) ? esc_attr_e( 'nav-tab-active', 'dt_manychat' ) : print ''; ?>"><?php esc_attr_e( 'Instructions', 'dt_manychat' ) ?></a>
            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $object = new DT_Manychat_Tab_General();
                    $object->content();
                    break;
                case "second":
                    $object = new DT_Manychat_Tab_Second();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }
}

/**
 * Class DT_Manychat_Tab_General
 */
class DT_Manychat_Tab_General
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>

        <?php
        $post_ids = Site_Link_System::get_list_of_sites_by_type( [ 'manychat' ], 'post_ids' );
        if ( ! empty( $post_ids ) ) {
            foreach ( $post_ids as $post_id ) {
                ?>
                <!-- Box -->
                <H1>Site Connection: <?php echo get_the_title($post_id); ?></H1><hr>

                <table class="widefat striped" style="border-width: 5px;">
                    <thead>
                    <th><h2>FOR CREATING A NEW RECORD</h2></th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>URL</th>
                                <th></th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <strong>Request Type:</strong><br><code>POST</code>
                                    </td>
                                    <td>
                                        <strong>Request URL:</strong><br><code><?php echo rest_url() . 'dt-public/v1/manychat/'; ?></code>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>Headers</th>
                                <th></th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <strong>Key</strong>
                                    </td>
                                    <td>
                                        <strong>Value</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>token</code></td>
                                    <td><code><?php echo get_post_meta($post_id, 'token', true) ?></code></td>
                                </tr>
                                <tr>
                                    <td><code>action</code></td>
                                    <td><code>create</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>Body</th>
                                </thead>
                                <tbody>
                                <tbody>
                                <tr>
                                    <td>
                                        <code>Add Full Subscriber Data</code> Note: this is a pre-defined selection in the manychat setup box.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>Response mapping</th>
                                <th></th>
                                </thead>
                                <tbody>
                                <tbody>
                                <tr>
                                    <td>
                                        <strong>JSONPath</strong><br><code>$.post_id</code>
                                    </td>
                                    <td>
                                        <strong>Select Custom Field</strong><br><code>dt_post_id</code> Note: You will need to create this custom field "dt_post_id".
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <br>


                <table class="widefat striped" style="border-width: 5px;">
                    <thead>
                    <th><h2>FOR LOGGING COMMENTS</h2></th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>URL</th>
                                <th></th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <strong>Request Type:</strong><br><code>POST</code>
                                    </td>
                                    <td>
                                        <strong>Request URL:</strong><br><code><?php echo rest_url() . 'dt-public/v1/manychat/'; ?></code>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>Headers</th>
                                <th></th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <strong>Key</strong>
                                    </td>
                                    <td>
                                        <strong>Value</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>token</code></td>
                                    <td><code><?php echo get_post_meta($post_id, 'token', true) ?></code></td>
                                </tr>
                                <tr>
                                    <td><code>action</code></td>
                                    <td><code>comment</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <thead>
                                <th>Body</th>
                                </thead>
                                <tbody>
                                <tbody>
                                <tr>
                                    <td>
                                    <code>{"post_id": dt_post_id,"message": "Second Step","skip_notification": true}</code>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <br><br>
                <!-- End Box -->
                <?php
            }

        }
        else {
            ?>
            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <th>Setup Instructions</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        You need to setup a "manychat" site to site link. <a href="<?php echo esc_url( admin_url() ) . 'post-new.php?post_type=site_link_system'  ?>">Create New Site Link</a>

                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->
            <?php
        }

    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Information</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

}

/**
 * Class DT_Manychat_Tab_Second
 */
class DT_Manychat_Tab_Second
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <hr>
        <h1>Initial Setup Instructions</h1>
        <ol>
            <li>
                Create a "ManyChat" Site-to-site Link. <a href="<?php echo esc_url( admin_url() ) . '/post-new.php?post_type=site_link_system'  ?>">Create new link</a>
                <ol style="list-style-type: lower-alpha;">
                    <li>Give the link any title you want.</li>
                    <li>Add site #1 as "manychat"</li>
                    <li>Add site #2 as the current site. (Hint: Use the auto fill link)</li>
                    <li>Set the type to "ManyChat".</li>
                </ol>
            </li>
            <li>
                Make sure you have configuration information on the "Configuration" Tab. <a href="<?php echo esc_url( admin_url() ) . 'admin.php?page=dt_manychat&tab=general'  ?>">Configuration Tab</a>
            </li>
            <li>
                In ManyChat create an "Action" in one of your workflows. This action needs to be an "External Request".
                <ol style="list-style-type: lower-alpha;">
                    <li>Add External Request Action step to a "Flow"</li>
                    <li>Open External Request Action dialogue box.</li>
                    <li>Transfer the connection information from the "Configuration" Tab under the heading "For Creating A New Record" to the fields in the External Request dialogue box.</li>
                    <ol style="list-style-type: lower-roman;">
                        <li>Set Request Type to POST</li>
                        <li>Copy URL to Request URL box.</li>
                        <li>Add to "Header" section two key/value fields: token: {provided value from configuration tab}, and action: "create" </li>
                        <li>Add to "Body" section the pre-defined "Add Full Subscriber Data"</li>
                        <li>Add to "Response mapping" section, JSONPath: '$.post_id', Select Custom Field: 'dt_post_id'. Note: add the custom field 'dt_post_id' if you haven't already.</li>
                    </ol>
                </ol>
            </li>
            <li>
                Test connection. You should see a new contact created in Disciple Tools. You will also get a response of 200/success.
            </li>
        </ol>
        <hr>
        <h1>Comment Setup Instructions</h1>

        <ol>
            <li>Make sure you have gone through the setup steps above.</li>
            <li>
                In ManyChat create an "Action" in one of your workflows. This action needs to be an "External Request".
                <ol style="list-style-type: lower-alpha;">
                    <li>Add External Request Action step to a "Flow"</li>
                    <li>Open External Request Action dialogue box.</li>
                    <li>Transfer the connection information from the "Configuration" Tab under the heading "For Logging Comments" to the fields in the External Request dialogue box.</li>
                    <ol style="list-style-type: lower-roman;">
                        <li>Set Request Type to POST</li>
                        <li>Copy URL to Request URL box.</li>
                        <li>Add to "Header" section two key/value fields: token: {provided value from configuration tab}, and action: "comment" </li>
                        <li>Add to "Body" section the pre-defined string provided in the configuration body section. This is a JSON string and must be copied exactly.<br>
                            <ol style="list-style-type: lower-alpha;">
                                <li>"post_id" = (int) This is the Contact record id from Disciple Tools that was saved during the create record process. You can also add this to a record directly through their contact page in Manychat.</li>
                                <li>Note: <code>dt_post_id</code> is the live variable added from the custom field drop down. This custom field must be created before it will show up in the drop down.</li>
                                <li>"message" = (string) This can be any string of any length. It will be logged into the comments area of the contact record.</li>
                                <li>"skip_notification" = (bool) This is either set to true or false and it controls whether the contact owner in Disciple Tools gets a notification that the comment was added. True means "do not notify", False means notify.</li>
                            </ol>
                        </li>
                    </ol>
                </ol>
            </li>
            <li>
                Test connection. You should see a new contact created in Disciple Tools. You will also get a response of 200/success.
            </li>
        </ol>
        <hr>
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Information</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

