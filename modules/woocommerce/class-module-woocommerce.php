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

            $this->includes = array();
        }

        /**
         * Define hooks
         *
         * @since    1.0.0
         * @param    Blocks_For_Products      $core   The Core object
         */
        public function define_hooks() {
            $this->core->add_filter( 'use_block_editor_for_post_type', array( $this, 'use_block_editor_for_post_type' ), 99, 2 );

            $this->core->add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            $this->core->add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );

            $this->core->add_action( 'load-post.php', array( $this, 'edit_page_init' ) );
            $this->core->add_action( 'load-post-new.php', array( $this, 'edit_page_init' ) );
            $this->core->add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
            $this->core->add_action( 'edit_form_after_editor', array( $this, 'edit_form_after_editor' ) );
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

            if ( empty( $_GET['post'] ) ) {
                return false;
            }

            $post = get_post( sanitize_text_field( $_GET['post'] ) );

            return $this->is_using_blocks() && ! $this->edit_post_with_classic_editor( $post );
        }

        /**
         * Action: 'admin_enqueue_scripts'
         * Add style to dashboard
         */
        public function admin_enqueue_scripts( $page ) {
            global $post;

            if ( ! in_array( $page, array( 'post.php', 'post-new.php' ), true ) ) {
                return;
            }

            if ( empty( $post ) || $post->post_type !== self::PRODUCT_POST_TYPE ) {
                return;
            }

            wp_enqueue_style( 'bfp-edit-content-style', BFP_PLUGIN_URL . '/modules/woocommerce/assets/css/edit-content.min.css', array(), BFP_VERSION );
        }

        /**
         * Action: 'enqueue_block_editor_assets'
         * Add script to block editor
         */
        public function enqueue_block_editor_assets() {
            global $post;

            if ( empty( $post ) || $post->post_type !== self::PRODUCT_POST_TYPE || $this->edit_post_with_classic_editor( $post ) ) {
                return;
            }

            $asset_file = include BFP_PLUGIN_PATH . '/build/block-editor.asset.php';
            wp_enqueue_script( 'bfp-block-editor-script', BFP_PLUGIN_URL . '/build/block-editor.min.js', $asset_file['dependencies'], $asset_file['version'], true );
        }

        /**
         * Action: 'load-post.php'
         * Do stuff on admin pages
         *
         * @SuppressWarnings(PHPMD.ExitExpression)
         */
        public function edit_page_init() {
            if ( empty( $_GET['post'] ) || empty( $_GET['_wpnonce'] ) ) {
                return;
            }

            $post  = get_post( sanitize_text_field( $_GET['post'] ) );
            $nonce = sanitize_text_field( $_GET['_wpnonce'] );

            if ( empty( $post ) || $post->post_type !== self::PRODUCT_POST_TYPE ) {
                return;
            }

            if ( ! empty( $_GET['remove-blocks'] ) && wp_verify_nonce( $nonce, 'bcp-remove-blocks' ) ) {
                update_post_meta( $post->ID, self::META_KEY_CLASSIC_EDITOR, '1' );

                wp_redirect( remove_query_arg( array( 'remove-blocks', 'blocks', '_wpnonce' ), false ), 302 );
                exit;
            }

            if ( ! empty( $_GET['use-blocks'] ) && wp_verify_nonce( $nonce, 'bcp-use-blocks' ) ) {
                delete_post_meta( $post->ID, self::META_KEY_CLASSIC_EDITOR );

                wp_redirect( remove_query_arg( array( 'use-blocks', 'blocks', '_wpnonce' ), false ), 302 );
                exit;
            }
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
            if ( $screen->action === 'add' ) {
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
            if ( empty( $_GET['blocks'] ) ) {
                return false;
            }

            return sanitize_text_field( $_GET['blocks'] ) === '1';
        }

        /**
         * Check we are using blocks for edit a post
         *
         * @return boolean
         */
        private function edit_post_with_classic_editor( $post ) {
            if ( empty( $post ) || empty( $post->ID ) ) {
                return false;
            }

            return get_post_meta( $post->ID, self::META_KEY_CLASSIC_EDITOR, true ) === '1';
        }

    }

}

