/**
 * Main JavaScript for Qurban Management System
 * Handles interactive elements and responsive behavior
 */

document.addEventListener('DOMContentLoaded', function() {
  // Mobile navigation toggle
  const menuToggle = document.querySelector('.navbar-menu');
  const navbarNav = document.querySelector('.navbar-nav');

  if (menuToggle && navbarNav) {
    menuToggle.addEventListener('click', function() {
      navbarNav.classList.toggle('show');
    });
  }

  // Form validation
  const forms = document.querySelectorAll('.needs-validation');

  forms.forEach(form => {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }

      form.classList.add('was-validated');
    }, false);
  });

  // Add animation to cards
  const animateElements = document.querySelectorAll('.animate-on-scroll');

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('fade-in');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    animateElements.forEach(el => observer.observe(el));
  } else {
    // Fallback for older browsers
    animateElements.forEach(el => el.classList.add('fade-in'));
  }

  // Add smooth hover effects to interactive elements
  const interactiveElements = document.querySelectorAll('.card, .btn');

  interactiveElements.forEach(el => {
    el.classList.add('hover-lift', 'hover-shadow');
  });

  // Format currency inputs
  const currencyInputs = document.querySelectorAll('.currency-input');

  currencyInputs.forEach(input => {
    input.addEventListener('input', function(e) {
      // Remove non-digits
      let value = this.value.replace(/[^\d]/g, '');

      // Format with thousand separator
      if (value.length > 0) {
        value = parseInt(value, 10).toLocaleString('id-ID');
      }

      this.value = value;
    });
  });

  // Table row highlighting
  const tableRows = document.querySelectorAll('table tbody tr');

  tableRows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      this.style.backgroundColor = 'var(--gray-100)';
    });

    row.addEventListener('mouseleave', function() {
      this.style.backgroundColor = '';
    });
  });

  // Make tables responsive on mobile
  const tables = document.querySelectorAll('.table');

  tables.forEach(table => {
    const headerCells = table.querySelectorAll('th');
    const headerTexts = Array.from(headerCells).map(th => th.textContent);

    if (window.innerWidth < 768) {
      table.classList.add('table-mobile-stack');

      const dataCells = table.querySelectorAll('tbody td');
      dataCells.forEach((td, index) => {
        const headerIndex = index % headerTexts.length;
        td.setAttribute('data-label', headerTexts[headerIndex]);
      });
    }
  });
});