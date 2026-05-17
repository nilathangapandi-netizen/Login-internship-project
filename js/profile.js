// js/profile.js
$(document).ready(function () {
  // Guard: redirect if not logged in
  const userData = localStorage.getItem("auth_user");
  if (!userData) {
    window.location.href = "login.html";
    return;
  }

  const user = JSON.parse(userData);

  // Populate read-only account fields
  $("#acc-name").val(user.name);
  $("#acc-username").val(user.username);
  $("#acc-email").val(user.email);

  // Load MongoDB profile details
  $.ajax({
    url: "php/profile.php",
    method: "GET",
    data: { action: "get", user_id: user.id, token: user.token },
    success: function (res) {
      const data = typeof res === "string" ? JSON.parse(res) : res;
      if (data.success && data.profile) {
        const p = data.profile;
        $("#age").val(p.age || "");
        $("#dob").val(p.dob || "");
        $("#contact").val(p.contact || "");
        $("#city").val(p.city || "");
        $("#bio").val(p.bio || "");
      }
    }
  });

  // Save profile
  $("#save-btn").on("click", function () {
    const payload = {
      action:  "update",
      user_id: user.id,
      token:   user.token,
      age:     $("#age").val().trim(),
      dob:     $("#dob").val(),
      contact: $("#contact").val().trim(),
      city:    $("#city").val().trim(),
      bio:     $("#bio").val().trim()
    };

    $.ajax({
      url: "php/profile.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(payload),
      success: function (res) {
        const data = typeof res === "string" ? JSON.parse(res) : res;
        if (data.success) {
          showMsg("success", "Profile saved successfully!");
        } else {
          showMsg("danger", data.message || "Failed to save profile.");
        }
      },
      error: function () {
        showMsg("danger", "Server error. Please try again.");
      }
    });
  });

  // Logout
  $("#logout-btn").on("click", function () {
    $.ajax({
      url: "php/login.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({ action: "logout", token: user.token }),
      complete: function () {
        localStorage.removeItem("auth_user");
        window.location.href = "login.html";
      }
    });
  });

  function showMsg(type, msg) {
    $("#profile-msg").html(`<div class="alert alert-${type}">${msg}</div>`);
    setTimeout(() => $("#profile-msg").html(""), 3000);
  }
});
