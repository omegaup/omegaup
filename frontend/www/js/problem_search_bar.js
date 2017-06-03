omegaup.OmegaUp.on('ready', function() {
  let checkbox = document.querySelector('#high-quality-filter');
  checkbox.addEventListener('click', function() {
    $('tbody>tr').show();
    if (checkbox.checked) {
      $('tbody>tr:not(.high-quality)').hide();
    }
  });
});
