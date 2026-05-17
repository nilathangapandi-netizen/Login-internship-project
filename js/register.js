// js/register.js
$(document).ready(function () {
  $("#register-btn").on("click", function () {
    const name     = $("#name").val().trim();
    const email    = $("#email").val().trim();
    const username = $("#username").val().trim();
    const password = $("#password").val().trim();

    if (!name || !email || !username || !password) {
      showMsg("#register-msg", "danger", "All fields are required.");
      return;
    }

    $.ajax({
      url: "php/register.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({ name, email, username, password }),
      success: function (res) {
        const data = typeof res === "string" ? JSON.parse(res) : res;
        if (data.success) {
          showMsg("#register-msg", "success", "Registration successful! Redirecting...");
          setTimeout(() => { window.location.href = "login.html"; }, 1200);
        } else {
          showMsg("#register-msg", "danger", data.message || "Registration failed.");
        }
      },
      error: function () {
        showMsg("#register-msg", "danger", "Server error. Please try again.");
      }
    });
  });

  function showMsg(selector, type, msg) {
    $(selector).html(`<div class="alert alert-${type}">${msg}</div>`);
  }
});
