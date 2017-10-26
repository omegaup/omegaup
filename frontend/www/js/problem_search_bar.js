omegaup.OmegaUp.on('ready', function() {
  var checkbox = document.querySelector('#high-quality-filter');
  checkbox.addEventListener('click', function() {
    $('tbody>tr').show();
    if (checkbox.checked) {
      $('tbody>tr:not(.high-quality)').hide();
    }
  });

  $('.tag')
      .on('click', function(e) {
        e.preventDefault();
        path = $(this).attr('href');
        if (location.href.indexOf('tag') != -1) {
          path = location.href + '&tag[]=' + $(this).html();
        }
        if (location.href.indexOf($(this).html()) != -1) {
          return false;
        }
        window.location = path;
      });

  omegaup.UI.problemTypeahead($('#problem-search-box'));
});
