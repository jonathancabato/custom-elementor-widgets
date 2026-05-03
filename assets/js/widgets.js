/**
 * Custom Elements for Elementor — Widget Scripts
 *
 * Shared frontend JavaScript for custom widgets.
 * Per-widget handlers are registered using Elementor's frontend hooks, e.g.:
 *
 *   elementorFrontend.hooks.addAction(
 *     'frontend/element_ready/ce-widget-name.default',
 *     function( $scope ) {
 *       // widget init code
 *     }
 *   );
 */
(function ($) {
    'use strict';

    // ─── Post Slider ──────────────────────────────────────────────────────────
    //
    // Must be wrapped in elementor/frontend/init so that elementorFrontend
    // is guaranteed to exist before we register the hook.

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/ce-post-slider.default',
            function ($scope) {
                var $wrapper = $scope.find('.ce-post-slider');
                var $track = $wrapper.find('.ce-post-slider__track');
                var mode = $wrapper.data('mode') || 'horizontal';

                $track.bxSlider({
                    mode: mode,
                    pager: true,
                    controls: true,
                    auto: true,
                    autoHover: true,
                    speed: 500,
                    pause: 4000,
                    adaptiveHeight: mode === 'vertical'
                });
            }
        );
    });

})(jQuery);
