/**
 * Admin Panel JavaScript
 * College Student Portal
 * DataTables initialization, form validation, and AJAX operations
 */

"use strict";

// ============================================================================
// DOCUMENT READY
// ============================================================================
document.addEventListener("DOMContentLoaded", function () {
  initDataTables();
  initFormValidation();
  initDeleteConfirmation();
  initSidebarToggle();
  initUniqueValidation();
  initAlertDismiss();
});

// ============================================================================
// DATATABLES INITIALIZATION
// ============================================================================

/**
 * Initialize DataTables with standard configuration
 */
function initDataTables() {
  const tables = document.querySelectorAll(".data-table");

  tables.forEach(function (table) {
    if (typeof $.fn.DataTable === "undefined") {
      console.warn("DataTables library not loaded");
      return;
    }

    $(table).DataTable({
      responsive: true,
      searchDelay: 500, // Prevent search spam
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ],
      order: [[0, "asc"]],
      language: {
        search: "Search:",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "No entries found",
        infoFiltered: "(filtered from _MAX_ total entries)",
        paginate: {
          first: "First",
          last: "Last",
          next: "Next",
          previous: "Previous",
        },
      },
      columnDefs: [
        {
          targets: "no-sort",
          orderable: false,
        },
      ],
    });
  });
}

// ============================================================================
// FORM VALIDATION
// ============================================================================

/**
 * Initialize Bootstrap form validation
 */
function initFormValidation() {
  const forms = document.querySelectorAll(".needs-validation");

  forms.forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        // Additional custom validation
        const isValid = performCustomValidation(form);

        if (!form.checkValidity() || !isValid) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add("was-validated");
      },
      false
    );
  });
}

/**
 * Perform custom validation beyond HTML5
 */
function performCustomValidation(form) {
  let isValid = true;

  // Password confirmation
  const password = form.querySelector(
    '[name="password"], [name="new_password"]'
  );
  const confirmPassword = form.querySelector('[name="confirm_password"]');

  if (password && confirmPassword && password.value && confirmPassword.value) {
    if (password.value !== confirmPassword.value) {
      showFieldError(confirmPassword, "Passwords do not match");
      isValid = false;
    }
  }

  // Email validation
  const email = form.querySelector('[name="email"]');
  if (email && email.value && !validateEmail(email.value)) {
    showFieldError(email, "Please enter a valid email address");
    isValid = false;
  }

  // Phone validation (if provided)
  const phone = form.querySelector('[name="phone"]');
  if (phone && phone.value && !validatePhone(phone.value)) {
    showFieldError(phone, "Please enter a valid phone number (10-15 digits)");
    isValid = false;
  }

  return isValid;
}

/**
 * Validate email format
 */
function validateEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

/**
 * Validate phone number
 */
function validatePhone(phone) {
  const digits = phone.replace(/\D/g, "");
  return digits.length >= 10 && digits.length <= 15;
}

// ============================================================================
// DELETE CONFIRMATION
// ============================================================================

/**
 * Initialize delete confirmation dialogs
 */
function initDeleteConfirmation() {
  const deleteButtons = document.querySelectorAll(".btn-delete-confirm");

  deleteButtons.forEach(function (btn) {
    btn.addEventListener("click", function (event) {
      const message =
        this.dataset.message || "Are you sure you want to delete this item?";

      if (!confirm(message)) {
        event.preventDefault();
      }
    });
  });
}

// ============================================================================
// SIDEBAR TOGGLE (Mobile)
// ============================================================================

/**
 * Initialize sidebar toggle for mobile view
 */
function initSidebarToggle() {
  const toggleBtn = document.getElementById("sidebar-toggle");
  const sidebar = document.querySelector(".admin-sidebar");

  if (!toggleBtn || !sidebar) return;

  toggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("show");
  });

  // Close sidebar when clicking outside
  document.addEventListener("click", function (event) {
    if (
      sidebar.classList.contains("show") &&
      !sidebar.contains(event.target) &&
      event.target !== toggleBtn
    ) {
      sidebar.classList.remove("show");
    }
  });
}

// ============================================================================
// REAL-TIME UNIQUENESS VALIDATION
// ============================================================================

/**
 * Initialize real-time uniqueness checking for fields
 */
function initUniqueValidation() {
  const uniqueFields = document.querySelectorAll("[data-unique]");

  uniqueFields.forEach(function (field) {
    const fieldType = field.dataset.unique;
    const excludeId = field.dataset.excludeId || "";

    field.addEventListener(
      "blur",
      debounce(function () {
        if (field.value.trim()) {
          checkUniqueness(field, fieldType, excludeId);
        }
      }, 500)
    );
  });
}

/**
 * Check field uniqueness via AJAX
 */
function checkUniqueness(field, fieldType, excludeId) {
  const csrfToken = document.querySelector('[name="csrf_token"]');
  if (!csrfToken) return;

  const formData = new FormData();
  formData.append("csrf_token", csrfToken.value);
  formData.append("field", fieldType);
  formData.append("value", field.value.trim());
  if (excludeId) {
    formData.append("exclude_id", excludeId);
  }

  // Show loading indicator
  field.classList.remove("is-valid", "is-invalid");

  fetch("../ajax/check-unique.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.unique) {
        field.classList.add("is-valid");
        field.classList.remove("is-invalid");
        clearFieldError(field);
      } else {
        field.classList.add("is-invalid");
        field.classList.remove("is-valid");
        showFieldError(field, data.message || "This value already exists");
      }
    })
    .catch((error) => {
      console.error("Error checking uniqueness:", error);
    });
}

// ============================================================================
// ALERT MANAGEMENT
// ============================================================================

/**
 * Auto-dismiss alerts after 5 seconds
 */
function initAlertDismiss() {
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");

  alerts.forEach(function (alert) {
    setTimeout(function () {
      if (typeof bootstrap !== "undefined") {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }
    }, 5000);
  });
}

/**
 * Show alert message
 */
function showAlert(message, type = "success") {
  const alertContainer = document.getElementById("alert-container");
  if (!alertContainer) return;

  const alertType = type === "error" ? "danger" : type;
  const alertHtml = `
        <div class="alert alert-${alertType} alert-dismissible fade show" role="alert">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

  alertContainer.innerHTML = alertHtml;
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Show field validation error
 */
function showFieldError(field, message) {
  field.classList.add("is-invalid");

  let feedback = field.parentNode.querySelector(".invalid-feedback");
  if (!feedback) {
    feedback = document.createElement("div");
    feedback.className = "invalid-feedback";
    field.parentNode.appendChild(feedback);
  }
  feedback.textContent = message;
  feedback.style.display = "block";
}

/**
 * Clear field validation error
 */
function clearFieldError(field) {
  field.classList.remove("is-invalid");

  const feedback = field.parentNode.querySelector(".invalid-feedback");
  if (feedback) {
    feedback.textContent = "";
    feedback.style.display = "none";
  }
}

/**
 * Debounce function
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

/**
 * Format number with commas
 */
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Format date for display
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

// ============================================================================
// FORM HELPERS
// ============================================================================

/**
 * Reset form and validation states
 */
function resetForm(form) {
  form.reset();
  form.classList.remove("was-validated");

  const fields = form.querySelectorAll(".is-valid, .is-invalid");
  fields.forEach(function (field) {
    field.classList.remove("is-valid", "is-invalid");
  });

  const feedbacks = form.querySelectorAll(".invalid-feedback");
  feedbacks.forEach(function (feedback) {
    feedback.textContent = "";
  });
}

/**
 * Disable form during submission
 */
function disableForm(form) {
  const submitBtn = form.querySelector('[type="submit"]');
  const inputs = form.querySelectorAll("input, select, textarea, button");

  if (submitBtn) {
    submitBtn.dataset.originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
  }

  inputs.forEach(function (input) {
    input.disabled = true;
  });
}

/**
 * Enable form after submission
 */
function enableForm(form) {
  const submitBtn = form.querySelector('[type="submit"]');
  const inputs = form.querySelectorAll("input, select, textarea, button");

  if (submitBtn && submitBtn.dataset.originalText) {
    submitBtn.innerHTML = submitBtn.dataset.originalText;
  }

  inputs.forEach(function (input) {
    input.disabled = false;
  });
}

// ============================================================================
// FILTER FUNCTIONALITY
// ============================================================================

/**
 * Apply filter to DataTable
 */
function applyTableFilter(tableId, column, value) {
  const table = $("#" + tableId).DataTable();
  table.column(column).search(value).draw();
}

/**
 * Clear all filters
 */
function clearTableFilters(tableId) {
  const table = $("#" + tableId).DataTable();
  table.search("").columns().search("").draw();

  // Reset filter dropdowns
  const filterForm = document.querySelector("#" + tableId + "-filters");
  if (filterForm) {
    filterForm.reset();
  }
}
