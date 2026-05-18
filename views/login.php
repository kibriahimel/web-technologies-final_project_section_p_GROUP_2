<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library System — Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 24px; color: #333; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; margin-bottom: 16px; }
        button { width: 100%; padding: 12px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #4338ca; }
        .error { background: #fee2e2; color: #b91c1c; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>
<?php session_start(); ?>
<div class="card">
    <h2> Library System</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    <form action="../controllers/LoginSaveController.php" method="POST">
        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
