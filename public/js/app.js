// ============================================================
//  CV Analyzer — JavaScript
// ============================================================

function formatSize(bytes) {
  if (bytes < 1024) return bytes + ' o';
  if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' Ko';
  return (bytes / 1024 / 1024).toFixed(1) + ' Mo';
}

document.addEventListener('DOMContentLoaded', () => {

  // ── Upload zone drag & drop ───────────────────────────
  const uploadZone  = document.getElementById('uploadZone');
  const fileInput   = document.getElementById('cv_file');
  const filePreview = document.getElementById('filePreview');
  const fileNameEl  = document.getElementById('fileName');

  if (uploadZone && fileInput) {
    // ... (dragenter, dragover logic same as before) ...

    uploadZone.addEventListener('drop', e => {
      e.preventDefault();
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        fileInput.files = files;
        showFilePreview(files[0]);
      }
    });

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        showFilePreview(fileInput.files[0]);
      }
    });

    // Internal UI helper (fine to keep here or move out)
    function showFilePreview(file) {
      if (fileNameEl) fileNameEl.textContent = file.name + ' (' + formatSize(file.size) + ')';
      if (filePreview) filePreview.style.display = 'flex';
    }
  }

  // ── Clear file selection ──────────────────────────────
  globalThis.clearFile = function() {
    if (fileInput)   fileInput.value = '';
    if (filePreview) filePreview.style.display = 'none';
  };

  // ── Submit button loading state ───────────────────────
  const analyzeForm = document.getElementById('analyzeForm');
  const submitBtn   = document.getElementById('submitBtn');

  if (analyzeForm && submitBtn) {
    analyzeForm.addEventListener('submit', () => {
      const btnText    = submitBtn.querySelector('.btn-text');
      const btnLoading = submitBtn.querySelector('.btn-loading');
      if (btnText)    btnText.style.display    = 'none';
      if (btnLoading) btnLoading.style.display = 'inline';
      submitBtn.disabled = true;
    });
  }
  
  // ── Scroll to section on score click (result page) ───
  document.querySelectorAll('.score-overview-item[data-target]').forEach(item => {
    item.addEventListener('click', () => {
      const target = document.querySelector(item.dataset.target);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        target.style.outline = '2px solid var(--accent)';
        setTimeout(() => { target.style.outline = ''; }, 1500);
      }
    });
  });

  // ── Animate bars on load ──────────────────────────────
  // Force reflow for CSS transitions to kick in after DOM load
  document.querySelectorAll('.soi-bar-fill, .mini-bar-fill, .demo-bar-fill').forEach(el => {
    const w = el.style.width;
    el.style.width = '0';
    requestAnimationFrame(() => {
      requestAnimationFrame(() => { el.style.width = w; });
    });
  });

  // ── Auto-dismiss alerts ───────────────────────────────
  document.querySelectorAll('.alert-success').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity 0.4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    }, 4000);
  });

});
