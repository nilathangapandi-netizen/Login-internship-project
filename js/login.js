// js/login.js

$(document).ready(function () {

  $("#login-btn").on("click", function () {

    const username = $("#username").val().trim();
    const password = $("#password").val().trim();

    if (!username || !password) {

      showMsg("#login-msg", "danger", "Please enter username and password.");

      return;
    }

    $.ajax({

      url: "php/login.php",

      method: "POST",

      contentType: "application/json",

      dataType: "json",

      data: JSON.stringify({
        username,
        password
      }),

      success: function (data) {

        if (data.success) {

          localStorage.setItem(
            "auth_user",
            JSON.stringify({
              id: data.user.id,
              name: data.user.name,
              username: data.user.username,
              email: data.user.email,
              token: data.token
            })
          );

          showMsg(
            "#login-msg",
            "success",
            "Login successful! Redirecting..."
          );

          setTimeout(() => {

            window.location.href = "profile.html";

          }, 1000);

        } else {

          showMsg(
            "#login-msg",
            "danger",
            data.message || "Invalid credentials."
          );

        }

      },

      error: function (xhr, textStatus, errorThrown) {

        console.error('Login AJAX error:', textStatus, errorThrown, xhr.status, xhr.responseText);

        showMsg(
          "#login-msg",
          "danger",
          "Server error. Check console."
        );

      }

    });

  });

  function showMsg(selector, type, msg) {

    $(selector).html(
      `<div class="alert alert-${type}">${msg}</div>`
    );

  }

});