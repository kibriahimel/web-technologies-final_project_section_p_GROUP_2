<?php
// ============================================================
// views/register.php
// Registration page - submits via POST to do_register
// ============================================================

require_once 'views/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-wrapper">
    <div class="form-box" style="min-width: 33vw; width: 50%; min-height: 60vh;">
        <h2>Create Account</h2>

        <form action="index.php?page=do_register" method="POST" onsubmit="return validateRegisterForm();">

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" name="full_name" id="full_name" placeholder="Enter your full name">
                <span class="err-msg" id="name_err"></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" placeholder="Enter your email">
                <span class="err-msg" id="email_err"></span>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" name="password" id="password" placeholder="Minimum 6 characters">
                <span class="err-msg" id="pass_err"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password">
                <span class="err-msg" id="confirm_err"></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" name="phone" id="phone" placeholder="e.g. 01700000000">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" placeholder="Your home address (optional)"></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success" style="width:100%; padding:12px;">Register</button>
            </div>

            <p style="text-align:center; font-size:13px;">
                Already have an account? <a href="index.php?page=login">Login here</a>
            </p>

        </form>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>
