<?php
// ============================================================
// views/login.php
// Login page with client-side JS validation
// Form submits via POST to index.php?page=do_login
// ============================================================

require_once 'views/header.php';
?>

<div class="auth-wrapper">
    <div class="form-box" style="min-width: 16vw; width: 20%; min-height: 50vh;"> 
           <h2> Login</h2>

        <form action="index.php?page=do_login" method="POST" onsubmit="return validateLoginForm();">

            <div class="form-group">
                <label for="email">Email Address </label>
                <input type="email" name="email" id="email" placeholder="Enter your email">
                <span class="err-msg" id="email_err"></span>
            </div>

            <div class="form-group">
                <label for="password">Password </label>
                <input type="password" name="password" id="password" placeholder="Enter your password">
                <span class="err-msg" id="pass_err"></span>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px;">Login</button>
            </div>

            <p style="text-align:center; font-size:13px;">
                Don't have an account? <a href="index.php?page=register">Register here</a>
            </p>

        </form>

        <!-- Demo credentials hint -->
        <div style="margin-top:40px; background:#f0f7ff; padding:12px; border-radius:4px; font-size:14px; color:#555;">
            <b>We Belive :</b><br><br>
            “A reader lives a thousand lives before he dies.” — George R. R. Martin <br><br>
            A library is not a luxury but one of the necessities of life.” — Henry Ward Beecher 
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>
