// js/profile.js

$(document).ready(function () {

    // Check login
    const userData = localStorage.getItem("auth_user");

    if (!userData) {

        window.location.href = "login.html";

        return;
    }

    const user = JSON.parse(userData);

    // Fill account info
    $("#acc-name").val(user.name || "");
    $("#acc-username").val(user.username || "");
    $("#acc-email").val(user.email || "");

    // ── LOAD PROFILE ─────────────────────────────

    $.ajax({

        url: "php/profile.php",

        method: "GET",

        dataType: "json",

        data: {
            action: "get",
            user_id: user.id,
            token: user.token
        },

        success: function (data) {

            if (data.success && data.profile) {

                const p = data.profile;

                $("#age").val(p.age || "");
                $("#dob").val(p.dob || "");
                $("#contact").val(p.contact || "");
                $("#city").val(p.city || "");
                $("#bio").val(p.bio || "");

            } else {

                showMsg(
                    "danger",
                    data.message || "Failed to load profile."
                );
            }
        },

        error: function (xhr) {

            console.error(
                "Profile load error:",
                xhr.responseText
            );

            let message = "Server error while loading profile.";

            try {

                const response = JSON.parse(xhr.responseText);

                if (response.message) {
                    message = response.message;
                }

            } catch (e) {}

            showMsg("danger", message);
        }
    });

    // ── SAVE PROFILE ─────────────────────────────

    $("#save-btn").on("click", function () {

        const payload = {

            action: "update",

            user_id: user.id,

            token: user.token,

            age: $("#age").val().trim(),

            dob: $("#dob").val(),

            contact: $("#contact").val().trim(),

            city: $("#city").val().trim(),

            bio: $("#bio").val().trim()
        };

        $("#save-btn")
            .prop("disabled", true)
            .text("Saving...");

        $.ajax({

            url: "php/profile.php",

            method: "POST",

            contentType: "application/json",

            dataType: "json",

            data: JSON.stringify(payload),

            success: function (data) {

                $("#save-btn")
                    .prop("disabled", false)
                    .text("Save Profile");

                if (data.success) {

                    showMsg(
                        "success",
                        "Profile saved successfully!"
                    );

                } else {

                    showMsg(
                        "danger",
                        data.message || "Failed to save profile."
                    );
                }
            },

            error: function (xhr) {

                $("#save-btn")
                    .prop("disabled", false)
                    .text("Save Profile");

                console.error(
                    "Save profile error:",
                    xhr.responseText
                );

                let message = "Server error while saving profile.";

                try {

                    const response = JSON.parse(xhr.responseText);

                    if (response.message) {
                        message = response.message;
                    }

                } catch (e) {}

                showMsg("danger", message);
            }
        });
    });

    // ── LOGOUT ─────────────────────────────

    $("#logout-btn").on("click", function () {

        $.ajax({

            url: "php/login.php",

            method: "POST",

            contentType: "application/json",

            data: JSON.stringify({
                action: "logout",
                token: user.token
            }),

            complete: function () {

                localStorage.removeItem("auth_user");

                window.location.href = "login.html";
            }
        });
    });

    // ── ALERT ─────────────────────────────

    function showMsg(type, msg) {

        $("#profile-msg").html(`
            <div class="alert alert-${type} alert-dismissible fade show">
                ${msg}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>
            </div>
        `);

        setTimeout(() => {

            $("#profile-msg").html("");

        }, 4000);
    }

});