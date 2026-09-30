document.addEventListener('DOMContentLoaded', function () {
  // Confirm delete
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });

  // Auto-generate slug preview
  const titleInput = document.querySelector('input[name="title_en"]');
  if (titleInput) {
    titleInput.addEventListener('input', function () {
      // (no live preview here, slug is generated server-side)
    });
  }
});
