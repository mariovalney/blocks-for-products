<div class="bcp-after-editor">
    <p>
        <?php esc_html_e( 'You disabled blocks for this product.', BFP_TEXTDOMAIN ); ?>
        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'use-blocks', '1' ), 'bcp-use-blocks' ) ); ?>" class="button-link" title="<?php esc_attr_e( 'Clicking here we will enable blocks again.', BFP_TEXTDOMAIN ); ?>">
            <?php esc_html_e( 'Back to blocks?', BFP_TEXTDOMAIN ); ?>
        </a>
    </p>
</div>
