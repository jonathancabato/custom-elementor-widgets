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
                var pager = $wrapper.data('pager') || 'basic';

                // Collect thumbnail URLs from each slide before bxSlider clones them.
                var thumbs = [];
                $track.find('li').each(function () {
                    thumbs.push($(this).data('thumb') || '');
                });

                var options = {
                    mode: mode,
                    pager: pager !== 'none',
                    controls: true,
                    auto: true,
                    autoHover: true,
                    speed: 500,
                    pause: 4000,
                    adaptiveHeight: mode === 'vertical'
                };

                if (pager === 'thumbnail') {
                    options.buildPager = function (slideIndex) {
                        var src = thumbs[slideIndex] || '';
                        return src
                            ? '<img src="' + src + '" alt="" />'
                            : '<span>' + (slideIndex + 1) + '</span>';
                    };
                }

                var slider = $track.bxSlider(options);

                // ── SVG arrow icons ───────────────────────────────────────
                // Replace bxSlider's "Prev" / "Next" text with inline SVGs.
                var svgPrev = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
                var svgNext = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>';
                $wrapper.find('.bx-prev').html(svgPrev);
                $wrapper.find('.bx-next').html(svgNext);

                // ── Thumbnail pager: sliding window ───────────────────────
                // Shows at most MAX_PAGER_THUMBS items centred on the active
                // slide. Re-runs on every slide change so it always tracks
                // the current position. Not user-controllable.
                if (pager === 'thumbnail') {
                    var MAX_PAGER_THUMBS = 5;
                    var HALF = Math.floor(MAX_PAGER_THUMBS / 2);

                    function updatePagerWindow(currentIndex) {
                        var $items = $wrapper.find('.bx-pager-item');
                        var total = $items.length;
                        var start = currentIndex - HALF;
                        var end = currentIndex + HALF;

                        // Clamp window to valid range
                        if (start < 0) {
                            end = Math.min(total - 1, end - start);
                            start = 0;
                        }
                        if (end >= total) {
                            start = Math.max(0, start - (end - total + 1));
                            end = total - 1;
                        }

                        $items.each(function (i) {
                            $(this).toggle(i >= start && i <= end);
                        });
                    }

                    // Initial render
                    updatePagerWindow(0);

                    // Update on every slide transition
                    options.onSlideAfter = function ($el, oldIndex, newIndex) {
                        updatePagerWindow(newIndex);
                    };

                    // Re-init with the onSlideAfter callback now attached
                    slider.destroySlider();
                    slider = $track.bxSlider(options);

                    // Re-inject SVGs after re-init
                    $wrapper.find('.bx-prev').html(svgPrev);
                    $wrapper.find('.bx-next').html(svgNext);
                    updatePagerWindow(0);
                }
            }
        );
    });

    // ─── Post Grid ────────────────────────────────────────────────────────────

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/ce-post-grid.default',
            function ($scope) {
                var $grid = $scope.find('.ce-post-grid');
                var $inner = $grid.find('.ce-post-grid__grid');
                var $btn = $grid.find('.ce-post-grid__load-more');

                if (!$btn.length) return;

                var loading = false;
                var ppl = parseInt($grid.data('ppl'), 10) || 6;

                /**
                 * Build N skeleton card elements matching the real card structure.
                 * @param {number} count
                 * @returns {string} HTML string
                 */
                function buildSkeletons(count) {
                    var html = '';
                    for (var i = 0; i < count; i++) {
                        html +=
                            '<article class="ce-post-grid__card ce-post-grid__card--skeleton">' +
                            '<div class="ce-post-grid__card-link">' +
                            '<figure class="ce-post-grid__thumb"></figure>' +
                            '<div class="ce-post-grid__card-body">' +
                            '<span class="ce-post-grid__skel-block ce-post-grid__skel-block--cat"></span>' +
                            '<span class="ce-post-grid__skel-block ce-post-grid__skel-block--title"></span>' +
                            '<span class="ce-post-grid__skel-block ce-post-grid__skel-block--title-2"></span>' +
                            '<span class="ce-post-grid__skel-block ce-post-grid__skel-block--date"></span>' +
                            '</div>' +
                            '</div>' +
                            '</article>';
                    }
                    return html;
                }

                $btn.on('click', function () {
                    if (loading) return;

                    loading = true;
                    $btn.prop('disabled', true).addClass('ce-post-grid__load-more--loading');

                    // Inject skeleton placeholders immediately
                    $inner.append(buildSkeletons(ppl));

                    var nextPage = parseInt($grid.data('page'), 10) + 1;

                    $.ajax({
                        url: (typeof ceAjax !== 'undefined') ? ceAjax.url : '',
                        type: 'POST',
                        data: {
                            action: 'ce_post_grid_load_more',
                            nonce: (typeof ceAjax !== 'undefined') ? ceAjax.nonce : '',
                            page: nextPage,
                            ppl: ppl,
                            cats: $grid.data('cats') || '',
                            card_style: $grid.data('card-style') || 'standard'
                        },
                        success: function (res) {
                            // Remove skeletons regardless of outcome
                            $inner.find('.ce-post-grid__card--skeleton').remove();

                            if (res && res.success) {
                                $inner.append(res.data.html);
                                $grid.data('page', nextPage);

                                if (!res.data.has_more) {
                                    $btn.attr('hidden', true);
                                }
                            }
                        },
                        error: function () {
                            $inner.find('.ce-post-grid__card--skeleton').remove();
                        },
                        complete: function () {
                            loading = false;
                            $btn.prop('disabled', false).removeClass('ce-post-grid__load-more--loading');
                        }
                    });
                });
            }
        );
    });

})(jQuery);
