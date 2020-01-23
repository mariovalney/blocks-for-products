import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { ExternalLink } from '@wordpress/components';
import { addQueryArgs, removeQueryArgs } from '@wordpress/url';

registerPlugin( 'bfp-more-menu-item', {
    icon: 'arrow-left-alt',
    render: () => {
        window.history.replaceState('', '', addQueryArgs( window.location.href, { blocks: '1' } ));

        return(
            <PluginMoreMenuItem
                onClick = {() => {
                    window.location.href = removeQueryArgs( window.location.href, 'blocks' );
                }}
            >
                { __( 'Back to product', 'blocks-for-products' ) }
            </PluginMoreMenuItem>
        )
    }
} );
