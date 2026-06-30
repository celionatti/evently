/**
 * Profile Page — UI interaction script.
 *
 * In your PHP template, profile data is loaded server-side and forms
 * submit natively via POST. This script handles:
 *
 * 1. Phone number formatting on input.
 * 2. Social/website URL validation on blur.
 * 3. Client-side form validation before allowing native POST.
 * 4. Status message display helper.
 */
(function () {
  'use strict';

  var form = document.getElementById('profile-form');
  var messageEl = document.getElementById('profile-message');
  var phoneInput = document.getElementById('profile-phone');
  var socialInput = document.getElementById('profile-social');

  // ─── Status Message ──────────────────────────────────────
  function showMessage(text, type) {
    if (!messageEl) return;
    messageEl.textContent = text;
    messageEl.className = 'auth-message ' + (type || 'info');
    messageEl.style.display = 'block';
    setTimeout(function () {
      messageEl.style.display = 'none';
    }, 4000);
  }

  // ─── Validation Helpers ──────────────────────────────────
  function digitsCount(val) {
    return (val || '').replace(/\D/g, '').length;
  }

  function validatePhone(val) {
    var d = digitsCount(val);
    return d === 0 || (d >= 7 && d <= 15);
  }

  function validateSocial(val) {
    if (!val) return true;
    val = val.trim();
    if (val.charAt(0) === '@') return /^@[\w.]{2,30}$/.test(val);
    try {
      var u = new URL(val);
      return u.protocol === 'http:' || u.protocol === 'https:';
    } catch (e) {
      return false;
    }
  }

  // ─── Phone Formatter ─────────────────────────────────────
  function formatPhoneInput(input) {
    if (!input) return;
    var v = input.value || '';
    var leadingPlus = v.trim().charAt(0) === '+';
    var nums = v.replace(/[^\d]/g, '');
    if (nums.length === 0) {
      input.value = '';
      return;
    }
    if (nums.length > 10) {
      var cc = nums.slice(0, nums.length - 10);
      var rest = nums.slice(-10);
      input.value =
        (leadingPlus ? '+' : '') +
        cc + ' ' + rest.slice(0, 3) + ' ' + rest.slice(3, 6) + ' ' + rest.slice(6);
    } else if (nums.length > 6) {
      input.value =
        (leadingPlus ? '+' : '') +
        nums.slice(0, 3) + ' ' + nums.slice(3, 6) + ' ' + nums.slice(6);
    } else if (nums.length > 3) {
      input.value = (leadingPlus ? '+' : '') + nums.slice(0, 3) + ' ' + nums.slice(3);
    } else {
      input.value = (leadingPlus ? '+' : '') + nums;
    }
  }

  if (phoneInput) {
    phoneInput.addEventListener('input', function () {
      formatPhoneInput(phoneInput);
    });
  }

  if (socialInput) {
    socialInput.addEventListener('blur', function () {
      if (!validateSocial(socialInput.value)) {
        socialInput.classList.add('invalid');
      } else {
        socialInput.classList.remove('invalid');
      }
    });
  }

  // ─── Form Validation ─────────────────────────────────────
  if (form) {
    form.addEventListener('submit', function (e) {
      var phone = (phoneInput || {}).value || '';
      var social = (socialInput || {}).value || '';

      if (!validatePhone(phone)) {
        e.preventDefault();
        showMessage('Phone number looks invalid. Please check it.', 'error');
        if (phoneInput) phoneInput.focus();
        return;
      }

      if (!validateSocial(social)) {
        e.preventDefault();
        showMessage('Website or social handle looks invalid.', 'error');
        if (socialInput) socialInput.focus();
        return;
      }

      // Allow native form submission to proceed
    });
  }
})();
