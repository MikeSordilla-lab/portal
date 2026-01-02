/**
 * Student Portal JavaScript
 * College Student Portal
 * Form validation, AJAX helpers, and UI interactions
 */

"use strict";

// ============================================================================
// DOCUMENT READY
// ============================================================================
document.addEventListener("DOMContentLoaded", function () {
  initFormValidation();
  initAlertDismiss();
  initProfileForm();
  initPasswordToggle();
  initPrintButton();
});

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
        if (!form.checkValidity()) {
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
 * Validate email format
 */
function validateEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

/**
 * Validate phone number (10-15 digits)
 */
function validatePhone(phone) {
  const digits = phone.replace(/\D/g, "");
  return digits.length >= 10 && digits.length <= 15;
}

/**
 * Validate password (minimum 8 characters)
 */
function validatePassword(password) {
  return password.length >= 8;
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function validateDate(date) {
  const regex = /^\d{4}-\d{2}-\d{2}$/;
  if (!regex.test(date)) return false;

  const d = new Date(date);
  return d instanceof Date && !isNaN(d);
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
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
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

  // Auto-dismiss after 5 seconds
  setTimeout(function () {
    const alert = alertContainer.querySelector(".alert");
    if (alert) {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }
  }, 5000);
}

// ============================================================================
// PROFILE MANAGEMENT
// ============================================================================

/**
 * Initialize profile form with AJAX submission
 */
function initProfileForm() {
  const profileForm = document.getElementById("profile-form");

  if (!profileForm) return;

  profileForm.addEventListener("submit", function (event) {
    event.preventDefault();

    if (!validateProfileForm(profileForm)) {
      return;
    }

    submitProfileForm(profileForm);
  });
}

/**
 * Validate profile form before submission
 */
function validateProfileForm(form) {
  const email = form.querySelector('[name="email"]');
  const phone = form.querySelector('[name="phone"]');

  let isValid = true;

  // Validate email
  if (email && email.value && !validateEmail(email.value)) {
    showFieldError(email, "Please enter a valid email address");
    isValid = false;
  } else if (email) {
    clearFieldError(email);
  }

  // Validate phone (if provided)
  if (phone && phone.value && !validatePhone(phone.value)) {
    showFieldError(phone, "Please enter a valid phone number (10-15 digits)");
    isValid = false;
  } else if (phone) {
    clearFieldError(phone);
  }

  return isValid;
}

/**
 * Submit profile form via AJAX
 */
function submitProfileForm(form) {
  const submitBtn = form.querySelector('[type="submit"]');
  const originalText = submitBtn.innerHTML;

  // Disable button and show loading
  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

  const formData = new FormData(form);

  fetch("../ajax/update-profile.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert(data.message, "success");
      } else {
        showAlert(data.message || "An error occurred", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert("An error occurred while saving your profile", "error");
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
}

// ============================================================================
// PHOTO UPLOAD
// ============================================================================

/**
 * Initialize photo upload functionality
 */
function initPhotoUpload() {
  const photoInput = document.getElementById("profile-picture-input");
  const photoPreview = document.getElementById("profile-picture-preview");

  if (!photoInput) return;

  photoInput.addEventListener("change", function (event) {
    const file = event.target.files[0];

    if (!file) return;

    // Validate file type
    if (!["image/jpeg", "image/png"].includes(file.type)) {
      showAlert("Only JPG and PNG files are allowed", "error");
      photoInput.value = "";
      return;
    }

    // Validate file size (2MB max)
    if (file.size > 2097152) {
      showAlert("File size exceeds 2MB limit", "error");
      photoInput.value = "";
      return;
    }

    // Preview image
    if (photoPreview) {
      const reader = new FileReader();
      reader.onload = function (e) {
        photoPreview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    // Upload file
    uploadProfilePhoto(file);
  });
}

/**
 * Upload profile photo via AJAX
 */
function uploadProfilePhoto(file) {
  const csrfToken = document.querySelector('[name="csrf_token"]');
  if (!csrfToken) return;

  const formData = new FormData();
  formData.append("csrf_token", csrfToken.value);
  formData.append("profile_picture", file);

  fetch("../ajax/upload-photo.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert(data.message, "success");
        // Update preview with new filename
        const preview = document.getElementById("profile-picture-preview");
        if (preview && data.filename) {
          preview.src =
            "../uploads/profile_pictures/" + data.filename + "?" + Date.now();
        }
      } else {
        showAlert(data.message || "Upload failed", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert("An error occurred while uploading the photo", "error");
    });
}

// ============================================================================
// PASSWORD MANAGEMENT
// ============================================================================

/**
 * Initialize password toggle visibility
 */
function initPasswordToggle() {
  const toggleBtns = document.querySelectorAll(".password-toggle");

  toggleBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");

      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    });
  });
}

/**
 * Validate password change form
 */
function validatePasswordChange(form) {
  const newPassword = form.querySelector('[name="new_password"]');
  const confirmPassword = form.querySelector('[name="confirm_password"]');

  let isValid = true;

  // Check password length
  if (newPassword && !validatePassword(newPassword.value)) {
    showFieldError(newPassword, "Password must be at least 8 characters");
    isValid = false;
  } else if (newPassword) {
    clearFieldError(newPassword);
  }

  // Check passwords match
  if (
    confirmPassword &&
    newPassword &&
    confirmPassword.value !== newPassword.value
  ) {
    showFieldError(confirmPassword, "Passwords do not match");
    isValid = false;
  } else if (confirmPassword) {
    clearFieldError(confirmPassword);
  }

  return isValid;
}

// ============================================================================
// PRINT FUNCTIONALITY
// ============================================================================

/**
 * Initialize print button
 */
function initPrintButton() {
  const printBtns = document.querySelectorAll(".btn-print");

  printBtns.forEach(function (btn) {
    btn.addEventListener("click", function (event) {
      event.preventDefault();
      window.print();
    });
  });
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

  let feedback = field.nextElementSibling;
  if (!feedback || !feedback.classList.contains("invalid-feedback")) {
    feedback = document.createElement("div");
    feedback.className = "invalid-feedback";
    field.parentNode.insertBefore(feedback, field.nextSibling);
  }
  feedback.textContent = message;
}

/**
 * Clear field validation error
 */
function clearFieldError(field) {
  field.classList.remove("is-invalid");

  const feedback = field.nextElementSibling;
  if (feedback && feedback.classList.contains("invalid-feedback")) {
    feedback.textContent = "";
  }
}

/**
 * Format date for display
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

/**
 * Debounce function for search inputs
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
