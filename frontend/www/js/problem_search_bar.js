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
        var tags = getParameterByName('tag');
        var path = location.pathname + '?tag=' + $(this).html();
        if (tags) {
          if (tags.indexOf($(this).html()) != -1) {
            return false;
          }
          path = location.pathname + '?tag=' + tags + '-' + $(this).html();
        }
        window.location = path;
      });

  omegaup.UI.problemTypeahead($('#problem-search-box'));

  function getParameterByName(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)'),
        results = regex.exec(location.search);
    return results === null ?
               '' :
               decodeURIComponent(results[1].replace(/\+/g, ' '));
  }
});
