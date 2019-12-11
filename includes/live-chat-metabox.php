<?php

class DT_Manychat_Live_Chat_Box {

    public function detail_box( $section ) {
        if ( $section === 'manychat' ) :
            global $post;
            $url = get_post_meta( $post->ID, 'manychat_live_chat', true );
            ?>
            <a href="<?php echo esc_url( $url ) ?>" target="_blank" class="button">Launch ManyChat Live Chat</a>
        <?php
        endif;

    }

    public function filter_box( $sections, $post_type = '' ) {
        if ($post_type === "contacts") {
            global $post;
            if ( $post && get_post_meta( $post->ID, 'manychat_live_chat', true ) ) {
                $sections[] = 'manychat';
            }
        }
        return $sections;
    }


    public function __construct() {
        add_action( 'dt_details_additional_section', [ $this, 'detail_box' ] );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'filter_box' ], 999, 2 );
    }

}
new DT_Manychat_Live_Chat_Box();
