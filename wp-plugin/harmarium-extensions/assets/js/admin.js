/* Harmarium Extensions — admin JS */
(function ($) {
  'use strict';

  /* QR preview in post list — fetch on hover and cache in data attribute */
  $(document).on('mouseenter', '.column-hm_linked_product a, .column-hm_linked_portfolio a', function () {
    // No-op placeholder for future hover previews
  });

  /* Generate QR preview button in portfolio/product edit screens */
  $(document).on('click', '.hm-gen-qr', function (e) {
    e.preventDefault();
    var $btn   = $(this);
    var url    = $btn.data('url') || window.location.href;
    var $wrap  = $btn.closest('.hm-qr-wrap');
    var $prev  = $wrap.find('.hm-qr-preview');
    var ext    = window.HarmariumExt || {};

    $.get(ext.restUrl + 'qr', { url: url, size: 4 }, function (data) {
      $prev.html(data.svg);
    });
  });
})(jQuery);
