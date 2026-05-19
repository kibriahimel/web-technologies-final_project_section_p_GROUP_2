// ============================================================
// views/js/main.js
// Simple JavaScript for the Library Management System
// Uses XMLHttpRequest (NO fetch, NO jQuery, NO axios)
// ============================================================


// ============================================================
// AJAX: Search books without page reload
// Called from browse_books.php
// ============================================================
function searchBooksAjax() {
    var keyword = document.getElementById('search_input').value.trim();
    var resultDiv = document.getElementById('book_results');
    var msgDiv = document.getElementById('search-result-msg');

    if (keyword == '') {
        // If empty, reload all books
        loadAllBooks();
        return;
    }

    msgDiv.innerHTML = "Searching...";

    // Create XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Configure request: GET to ajax_search.php with keyword
    xhr.open("GET", "ajax_search.php?keyword=" + encodeURIComponent(keyword), true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                var data = JSON.parse(xhr.responseText);

                if (data.length == 0) {
                    msgDiv.innerHTML = "No books found for: <strong>" + keyword + "</strong>";
                    resultDiv.innerHTML = "";
                } else {
                    msgDiv.innerHTML = "Found <strong>" + data.length + "</strong> result(s) for: <strong>" + keyword + "</strong>";
                    buildBookGrid(data, resultDiv);
                }
            } catch (e) {
                msgDiv.innerHTML = "Search error. Try again.";
            }
        }
    };

    // Send the request
    xhr.send();
}

// ============================================================
// AJAX: Load all books (when search is cleared)
// ============================================================
function loadAllBooks() {
    var resultDiv = document.getElementById('book_results');
    var msgDiv = document.getElementById('search-result-msg');
    msgDiv.innerHTML = "Loading all books...";

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "ajax_search.php?keyword=", true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var data = JSON.parse(xhr.responseText);
            msgDiv.innerHTML = "Showing all <strong>" + data.length + "</strong> books.";
            buildBookGrid(data, resultDiv);
        }
    };

    xhr.send();
}

// ============================================================
// Build book cards dynamically from JSON data
// ============================================================
function buildBookGrid(books, container) {
    var html = '<div class="book-grid">';

    for (var i = 0; i < books.length; i++) {
        var book = books[i];
        var cover = book.cover_image ? book.cover_image : 'no_cover.png';
        var genre = book.genre_name ? book.genre_name : 'Unknown';

        html += '<div class="book-card">';
        html += '<img src="uploads/' + cover + '" alt="' + book.title + '" onerror="this.src=\'uploads/no_cover.png\'">';
        html += '<h4>' + book.title + '</h4>';
        html += '<p>' + book.author + '</p>';
        html += '<p style="color:#2c6e9e;">' + genre + '</p>';
        html += '<a href="index.php?page=book_detail&id=' + book.id + '" class="btn btn-primary btn-sm">View</a>';
        html += '</div>';
    }

    html += '</div>';
    container.innerHTML = html;
}


// ============================================================
// AJAX: Check book availability in real-time
// ============================================================
function checkAvailability(book_id, branch_id) {
    if (!branch_id) return;

    var resultSpan = document.getElementById('availability_result');
    if (!resultSpan) return;

    resultSpan.innerHTML = "Checking...";
    resultSpan.style.color = "#888";

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "ajax_availability.php?book_id=" + book_id + "&branch_id=" + branch_id, true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var data = JSON.parse(xhr.responseText);

            if (data.available > 0) {
                resultSpan.innerHTML = "&#10003; Available (" + data.available + " copies)";
                resultSpan.style.color = "#28a745";
            } else {
                resultSpan.innerHTML = "&#10007; Not Available at this branch";
                resultSpan.style.color = "#dc3545";
            }
        }
    };

    xhr.send();
}


// ============================================================
// Client-side Form Validation: Login Form
// ============================================================
function validateLoginForm() {
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value.trim();

    if (email == '') {
        showError('email_err', 'Email is required.');
        return false;
    }

    if (!isValidEmail(email)) {
        showError('email_err', 'Enter a valid email address.');
        return false;
    }

    if (password == '') {
        showError('pass_err', 'Password is required.');
        return false;
    }

    return true;
}

// ============================================================
// Client-side Form Validation: Register Form
// ============================================================
function validateRegisterForm() {
    var name     = document.getElementById('full_name').value.trim();
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value.trim();
    var confirm  = document.getElementById('confirm_password').value.trim();

    clearErrors();

    if (name.length < 3) {
        showError('name_err', 'Full name must be at least 3 characters.');
        return false;
    }

    if (!isValidEmail(email)) {
        showError('email_err', 'Enter a valid email address.');
        return false;
    }

    if (password.length < 6) {
        showError('pass_err', 'Password must be at least 6 characters.');
        return false;
    }

    if (password !== confirm) {
        showError('confirm_err', 'Passwords do not match.');
        return false;
    }

    return true;
}

// ============================================================
// Client-side Form Validation: Book Form
// ============================================================
function validateBookForm() {
    var title  = document.getElementById('title').value.trim();
    var author = document.getElementById('author').value.trim();

    if (title == '') {
        alert("Book title is required.");
        return false;
    }

    if (author == '') {
        alert("Author name is required.");
        return false;
    }

    return true;
}

// ============================================================
// Confirm Delete action
// ============================================================
function confirmDelete(message) {
    return confirm(message || "Are you sure you want to delete this?");
}

// ============================================================
// Helper: Show error below a field
// ============================================================
function showError(elementId, message) {
    var el = document.getElementById(elementId);
    if (el) {
        el.innerHTML = message;
        el.style.color = "#dc3545";
        el.style.fontSize = "12px";
    }
}

// ============================================================
// Helper: Clear all error messages
// ============================================================
function clearErrors() {
    var errors = document.querySelectorAll('.err-msg');
    for (var i = 0; i < errors.length; i++) {
        errors[i].innerHTML = '';
    }
}

// ============================================================
// Helper: Simple email format check
// ============================================================
function isValidEmail(email) {
    var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
}

// ============================================================
// Auto-hide alert messages after 4 seconds
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var alerts = document.querySelectorAll('.alert-success, .alert-error');
    for (var i = 0; i < alerts.length; i++) {
        (function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function () { alert.style.display = 'none'; }, 500);
            }, 4000);
        })(alerts[i]);
    }
});
