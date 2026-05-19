// js/login.js

$(document).ready(function () {

    $("#login-btn").on("click", function () {

        const username = $("#username").val().trim();
        const password = $("#password").val().trim();

        // Clear old message
        $("#login-msg").html("");

        // Validation
        if (!username || !password) {

            showMsg(
                "danger",
                "Please enter username and password."
            );

            return;
        }

        // Button loading state
        $("#login-btn")
            .prop("disabled", true)
            .html(`
                <span class="spinner-border spinner-border-sm"></span>
                Logging in...
            `);

        $.ajax({

            url: "php/login.php",

            method: "POST",

            contentType: "application/json",

            dataType: "json",

            data: JSON.stringify({
                username: username,
                password: password
            }),

            success: function (data) {

                // Reset button
                resetButton();

                // SUCCESS
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
                        "success",
                        `Welcome back, ${data.user.name}! Redirecting...`
                    );

                    setTimeout(() => {

                        window.location.href = "profile.html";

                    }, 1500);

                } else {

                    // ERROR MESSAGE FROM SERVER
                    showMsg(
                        "danger",
                        data.message || "Invalid login credentials."
                    );
                }
            },
            error: function (xhr) {

    resetButton();

    console.error(
        "Login AJAX error:",
        xhr.status,
        xhr.responseText
    );

    let message = "Server error. Please try again.";

    // Read backend JSON response
    if (xhr.responseJSON && xhr.responseJSON.message) {

        message = xhr.responseJSON.message;

    } else {

        try {

            const response = JSON.parse(xhr.responseText);

            if (response.message) {
                message = response.message;
            }

        } catch (e) {
            console.log(e);
        }
    }

    showMsg(
        "danger",
        message
    );
}
        });

    });

    // Reset button
    function resetButton() {

        $("#login-btn")
            .prop("disabled", false)
            .html("Login");
    }

    // Alert message
    function showMsg(type, msg) {

        $("#login-msg").html(`
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }

});