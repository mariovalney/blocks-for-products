<div class="postbox bcp-postbox">
    <h2><?php _e( 'Description', 'woocommerce' ); ?></h2>
    <div class="inside">
        <a href="<?php echo add_query_arg( 'blocks', '1' ); ?>" class="button button-primary button-large">
            <?php _e( 'Edit with Gutemberg', BFP_TEXTDOMAIN ); ?>
        </a>
        <a href="<?php echo wp_nonce_url( add_query_arg( 'remove-blocks', '1' ), 'bcp-remove-blocks' ); ?>" class="button-link" title="<?php esc_attr_e( 'Clicking here we will disable this feature to this post only.', BFP_TEXTDOMAIN ); ?>">
            <?php _e( 'Use Classic Editor', BFP_TEXTDOMAIN ); ?>
        </a>
    </div>
</div>
