<?php

/**
 * BFP_Module_Woocommerce
 * Class responsible to manage all WooCommerce stuff
 *
 * Depends: dependence
 *
 * @package         Blocks_For_Products
 * @subpackage      BFP_Module_Woocommerce
 * @since           1.0.0
 *
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( 'BFP_Module_Woocommerce' ) ) {

    class BFP_Module_Woocommerce {

        const PRODUCT_POST_TYPE = 'product';

        const META_KEY_CLASSIC_EDITOR = '_bfp_edit_with_classic_editor';

        /**
         * Run
         *
         * @since    1.0.0
         */
        public function run() {
            $module = $this->core->get_module( 'dependence' );

            // Checking Dependences
            $module->add_dependence( 'woocommerce/woocommerce.php', 'WooCommerce', 'woocommerce' );

            if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '<' ) ) {
                $notice = __( 'Please update <strong>WooCommerce</strong>. The minimum supported version is 2.2.', BFP_TEXTDOMAIN );
                $module->add_dependence_notice( $notice );
            }

            $this->includes = [];
        }

        /**
         * Define hooks
         *
         * @since    1.0.0
         * @param    Blocks_For_Products      $core   The Core object
         */
        public function define_hooks() {
            $this->core->add_filter( 'use_block_editor_for_post_type', [ $this, 'use_block_editor_for_post_type' ], 99, 2 );

            $this->core->add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            $this->core->add_action( 'load-post.php', [ $this, 'edit_page_init' ] );
            $this->core->add_action( 'load-post-new.php', [ $this, 'edit_page_init' ] );
            $this->core->add_action( 'edit_form_after_title', [ $this, 'edit_form_after_title' ] );
            $this->core->add_action( 'edit_form_after_editor', [ $this, 'edit_form_after_editor' ] );
        }

        /**
         * Action: 'enqueue_scripts'
         * Add style to dashboard
         */
        public function enqueue_scripts( $page ) {
            global $post;

            if ( ! in_array( $page, [ 'post.php', 'post-new.php' ], true ) ) {
                return;
            }

            if ( empty( $post ) || $post->post_type !== self::PRODUCT_POST_TYPE ) {
                return;
            }

            wp_enqueue_style( 'bfp-edit-content-style', BFP_PLUGIN_URL . '/modules/woocommerce/assets/css/edit-content.min.css' );
        }

        /**
         * Action: 'load-post.php'
         * Do stuff on admin pages
         */
        public function edit_page_init() {
            if ( empty( $_GET['post'] ) || empty( $_GET['_wpnonce'] ) ) {
                return;
            }

            $post = get_post( sanitize_text_field( $_GET['post'] ) );
            $nonce = sanitize_text_field( $_GET['_wpnonce'] );

            if ( empty( $post ) || $post->post_type !== self::PRODUCT_POST_TYPE ) {
                return;
            }

            if ( ! empty( $_GET['remove-blocks'] ) && wp_verify_nonce( $nonce, 'bcp-remove-blocks' ) ) {
                update_post_meta( $post->ID, self::META_KEY_CLASSIC_EDITOR, '1' );
            }

            if ( ! empty( $_GET['use-blocks'] ) && wp_verify_nonce( $nonce, 'bcp-use-blocks' ) ) {
                delete_post_meta( $post->ID, self::META_KEY_CLASSIC_EDITOR );
            }

            wp_redirect( remove_query_arg( [ 'use-blocks', 'remove-blocks', '_wpnonce' ], false ), 302 );
            exit;
        }

        /**
         * Filter: 'use_block_editor_for_post_type'
         * Activate Gutenberg for products post type.
         *
         * Note: we use 99 as priority to try to override themes and other plugins,
         * as we are creating a new interace to work with blocks on product pages.
         */
        public function use_block_editor_for_post_type( $can_edit, $post_type ) {
            if ( $post_type !== self::PRODUCT_POST_TYPE ) {
                return $can_edit;
            }

            return is_admin() && $this->is_using_blocks();
        }

        /**
         * Action: 'edit_form_after_title'
         * Render button to edit post with blocks and remove editor support
         */
        public function edit_form_after_title( $post ) {
            if ( $post->post_type !== self::PRODUCT_POST_TYPE || $this->edit_post_with_classic_editor( $post ) ) {
                return;
            }

            remove_post_type_support( self::PRODUCT_POST_TYPE, 'editor' );

            $screen = get_current_screen();
            if ($screen->action === 'add') {
                require BFP_PLUGIN_PATH . '/modules/woocommerce/views/new-editor.php';
                return;
            }

            require BFP_PLUGIN_PATH . '/modules/woocommerce/views/editor.php';
        }

        /**
         * Action: 'edit_form_after_editor'
         * Return editor support
         */
        public function edit_form_after_editor( $post ) {
            if ( $post->post_type !== self::PRODUCT_POST_TYPE ) {
                return;
            }

            if ( $this->edit_post_with_classic_editor( $post ) ) {
                require BFP_PLUGIN_PATH . '/modules/woocommerce/views/back-to-blocks.php';
                return;
            }

            add_post_type_support( self::PRODUCT_POST_TYPE, 'editor' );
        }

        /**
         * Check we are using blocks for edit a post
         *
         * @return boolean
         */
        private function is_using_blocks() {
            return $_GET['blocks'] ?? false;
        }

        /**
         * Check we are using blocks for edit a post
         *
         * @return boolean
         */
        private function edit_post_with_classic_editor( $post ) {
            return get_post_meta( $post->ID, self::META_KEY_CLASSIC_EDITOR, true ) === '1';
        }

    }

}

