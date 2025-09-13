/**
 * WP Secure Contact Form - Toast Notification Logic
 * Shows and fades out the toast message, then removes the wscf_status parameter from the URL.
 */
document.addEventListener("DOMContentLoaded", function() {
    var toast = document.getElementById("wscf-toast");
    if (toast) {
        toast.style.display = "block";
        setTimeout(function() {
            toast.style.opacity = "0";
        }, 3000);
        setTimeout(function() {
            toast.style.display = "none";
        }, 4000);
    }
    // Remove wscf_status from URL after showing the message
    if (window.history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.delete("wscf_status");
        window.history.replaceState({}, document.title, url.pathname + url.search);
    }
});