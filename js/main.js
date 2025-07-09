document.addEventListener('DOMContentLoaded', function() {
  const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltips.map(t => new bootstrap.Tooltip(t));

  window.showNotification = function(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '1100';
    notification.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
  };

  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
        submitBtn.disabled = true;
        setTimeout(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }, 1500);
      }
    });
  });
});