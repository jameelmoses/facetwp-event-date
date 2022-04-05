(function($) {
  FWP.hooks.addAction('facetwp/refresh/event_date', function($this, facet_name) {
    var val = $this.find('.facetwp-dropdown').val();
    FWP.facets[ facet_name ] = val ? [ val ] : [];
  });

  FWP.hooks.addFilter('facetwp/selections/event_date', function(output, params) {
    var $item = params.el.find('.facetwp-dropdown');
    if ($item.len()) {
      var dd = $item.nodes[0];
      var text = dd.options[ dd.selectedIndex ].text;
      return text.replace(/\(\d+\)$/, '');
    }
    return '';
  });

  $(document).on('change', '.facetwp-facet-event_type select', function() {
    var $facet = $(this).closest('.facetwp-facet');
    var facet_name = $facet.attr('data-name');
    FWP.autoload();
  });
})(fUtil);
