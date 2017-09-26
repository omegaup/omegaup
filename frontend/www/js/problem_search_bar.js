omegaup.OmegaUp.on('ready', function() {
  var checkbox = document.querySelector('#high-quality-filter');
  checkbox.addEventListener('click', function() {
    $('tbody>tr').show();
    if (checkbox.checked) {
      $('tbody>tr:not(.high-quality)').hide();
    }
  });
  omegaup.UI.problemTypeahead($('#problem-search-box'));
});
