document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', e => {
    if (!confirm(form.dataset.confirm)) e.preventDefault();
  }));
  document.querySelectorAll('[data-filter]').forEach(input => input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    document.querySelectorAll(input.dataset.filter).forEach(row => row.hidden = q && !row.textContent.toLowerCase().includes(q));
  }));
  setTimeout(() => document.querySelectorAll('.alert').forEach(el => el.classList.add('fade')), 5000);
});
