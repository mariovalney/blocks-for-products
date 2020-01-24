<div class="postbox bcp-postbox">
    <h2><?php esc_html_e( 'Description', 'woocommerce' ); ?></h2>
    <div class="inside">
        <a href="<?php echo esc_url( add_query_arg( 'blocks', '1' ) ); ?>" class="button button-primary button-large">
            <?php esc_html_e( 'Edit with blocks', BFP_TEXTDOMAIN ); ?>
        </a>
        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'remove-blocks', '1' ), 'bcp-remove-blocks' ) ); ?>" class="button-link" title="<?php esc_attr_e( 'Clicking here we will disable this feature to this post only.', BFP_TEXTDOMAIN ); ?>">
            <?php esc_html_e( 'Use Classic Editor', BFP_TEXTDOMAIN ); ?>
        </a>
    </div>
</div>
