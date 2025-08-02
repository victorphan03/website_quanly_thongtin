<?php
session_start();
include 'connect.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username=?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống quản lý tin tức</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 0; }
        .header-top { background: #ffe0b2; padding: 18px 0 5px 0; text-align: center; border-bottom: 1px solid #f7c873; }
        .header-top .icon { display: inline-block; vertical-align: middle; font-size: 28px; margin-right: 10px; }
        .header-top-title { display: inline-block; font-size: 24px; color: #d2691e; font-weight: bold; vertical-align: middle; }
        .header-top-desc { display: block; font-size: 13px; color: #888; margin-top: 2px; }
        .login-box { max-width: 350px; margin: 60px auto 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 16px #0002; padding: 30px 30px 25px 30px; }
        .login-box h2 { text-align: center; color: #1a73e8; margin-bottom: 25px; font-size: 22px; }
        .login-box label { display: block; margin-bottom: 7px; color: #444; font-size: 15px; }
        .login-box input[type="text"], .login-box input[type="password"] { width: 100%; padding: 10px 8px; margin-bottom: 18px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px; background: #f7fafd; }
        .login-box input[type="submit"] { width: 100%; background: #1a73e8; color: #fff; border: none; border-radius: 5px; padding: 11px 0; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .login-box input[type="submit"]:hover { background: #135ba1; }
        .login-box .error { color: #e53935; background: #fff3f3; border-radius: 4px; padding: 7px 10px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        @media (max-width: 500px) { .login-box { max-width: 98vw; margin-top: 20px; padding: 15px 5px; } .login-box h2 { font-size: 18px; } }
    </style>
</head>
<body>
    <div class="header-top">
        <span class="icon">📰</span>
        <div class="header-top-title">
            HỆ THỐNG QUẢN LÝ TIN TỨC
            <span class="header-top-desc">"Cập nhật từng khoảnh khắc – Lan tỏa mọi tin tức"</span>
        </div>
    </div>
    <div class="login-box">
        <h2>Đăng nhập hệ thống</h2>
        <form method="post" autocomplete="off">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" name="username" required autofocus>
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
            <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <input type="submit" value="Đăng nhập">
        </form>
    </div>
</body>
</html>
