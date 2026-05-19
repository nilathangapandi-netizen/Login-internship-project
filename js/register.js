// js/register.js

$(document).ready(function () {

    $("#register-btn").on("click", function () {

        const name = $("#name").val().trim();

        const email = $("#email").val().trim();

        const username = $("#username").val().trim();

        const password = $("#password").val().trim();

        // Clear old message
        $("#register-msg").html("");

        // Validation
        if (!name || !email || !username || !password) {

            showMsg(
                "danger",
                "All fields are required."
            );

            return;
        }

        // Email validation
        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {

            showMsg(
                "danger",
                "Please enter a valid email address."
            );

            return;
        }

        // Password validation
        if (password.length < 8) {

            showMsg(
                "danger",
                "Password must be at least 8 characters."
            );

            return;
        }

        // Loading state
        $("#register-btn")
            .prop("disabled", true)
            .html(`
                <span class="spinner-border spinner-border-sm"></span>
                Registering...
            `);

        $.ajax({

            url: "php/register.php",

            method: "POST",

            contentType: "application/json",

            dataType: "json",

            data: JSON.stringify({
                name: name,
                email: email,
                username: username,
                password: password
            }),

            success: function (data) {

                resetButton();

                if (data.success) {

                    showMsg(
                        "success",
                        "Registration successful! Redirecting..."
                    );

                    // Clear form
                    $("#name").val("");
                    $("#email").val("");
                    $("#username").val("");
                    $("#password").val("");

                    setTimeout(() => {

                        window.location.href =
                            "login.html";

                    }, 1500);

                } else {

                    showMsg(
                        "danger",
                        data.message ||
                        "Registration failed."
                    );
                }
            },

            error: function (xhr) {

                resetButton();

                console.error(
                    "Register AJAX error:",
                    xhr.responseText
                );

                let message =
                    "Server error. Please try again.";

                // Read backend JSON message
                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                } else {

                    try {

                        const response =
                            JSON.parse(xhr.responseText);

                        if (response.message) {

                            message =
                                response.message;
                        }

                    } catch (e) {}
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

        $("#register-btn")
            .prop("disabled", false)
            .html("Register");
    }

    // Alert message
    function showMsg(type, msg) {

        $("#register-msg").html(`
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                ${msg}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>
            </div>
        `);
    }

});