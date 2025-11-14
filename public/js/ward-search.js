document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('wardSearch');
  const table = document.getElementById('wardTable');
  if (!input || !table) return;

  input.addEventListener('input', function () {
    const term = input.value.toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = term === '' || text.includes(term) ? '' : 'none';
    });
  });
});
