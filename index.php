<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống quản lý tin tức</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        .header-top {
            background: #ffe0b2;
            padding: 18px 0 5px 0;
            text-align: center;
            border-bottom: 1px solid #f7c873;
        }
        .header-top .icon {
            display: inline-block;
            vertical-align: middle;
            font-size: 28px;
            margin-right: 10px;
        }
        .header-top-title {
            display: inline-block;
            font-size: 24px;
            color: #d2691e;
            font-weight: bold;
            vertical-align: middle;
        }
        .header-top-desc {
            display: block;
            font-size: 13px;
            color: #888;
            margin-top: 2px;
        }
        .center-box {
            max-width: 350px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 16px #0002;
            padding: 0 0 25px 0;
        }
        .center-box h2 {
            background: #1a73e8;
            color: #fff;
            margin: 0 auto;
            padding: 18px 0 13px 0;
            border-radius: 10px 10px 0 0;
            font-size: 22px;
            letter-spacing: 1px;
            text-align: center;
            width: 100%;
            display: block;
        }
        .menu-list {
            list-style: none;
            margin: 0;
            padding: 20px 0 0 0;
        }
        .menu-list li {
            margin-bottom: 18px;
        }
        .menu-link {
            display: flex;
            align-items: center;
            background: #f7fafd;
            border-radius: 6px;
            padding: 13px 18px;
            color: #333;
            text-decoration: none;
            font-size: 17px;
            font-weight: 500;
            box-shadow: 0 1px 4px #0001;
            transition: background 0.2s;
        }
        .menu-link:hover {
            background: #e3f0ff;
        }
        .menu-icon {
            font-size: 22px;
            margin-right: 13px;
        }
        .menu-list li:last-child .menu-link {
            background: #fff3f3;
            color: #d32f2f;
        }
        .menu-list li:last-child .menu-link:hover {
            background: #ffeaea;
        }
        .user-info {
            text-align: right;
            margin: 10px 20px 0 0;
            color: #1a73e8;
            font-size: 15px;
        }
        .logout-link {
            color: #fff;
            background: #e53935;
            border-radius: 4px;
            padding: 4px 12px;
            margin-left: 10px;
            text-decoration: none;
            font-size: 15px;
        }
        .logout-link:hover {
            background: #b71c1c;
        }
        @media (max-width: 500px) {
            .center-box { max-width: 98vw; margin-top: 20px; }
            .center-box h2 { font-size: 18px; }
            .menu-link { font-size: 15px; padding: 10px 8px; }
        }
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
    <nav class="navbar">
        <a href="index.php" class="nav-link">Home</a>
        <a href="quanlyloaitin.php" class="nav-link">Quản lý loại tin</a>
        <a href="quanlytin.php" class="nav-link">Quản lý tin tức</a>
        <a href="quanlyuser.php" class="nav-link">Quản lý User</a>
        <div class="navbar-user">
            <span class="user-icon">👤</span>
            <span class="user-name"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </nav>
    <style>
    .navbar {
        background: #222;
        padding: 0 0 0 10px;
        min-height: 38px;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-start;
        position: relative;
        padding: 0px 16px;
    }
    .nav-link {
        color: #fff;
        text-decoration: none;
        padding: 0 18px;
        line-height: 38px;
        display: inline-block;
        font-size: 16px;
        transition: background 0.2s;
    }
    .nav-link:hover {
        background: #444;
        color: #ffeb3b;
    }
    .navbar-user {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ffeb3b;
        font-size: 15px;
    }
    .user-icon {
        font-size: 18px;
        color: #ffeb3b;
    }
    .user-name {
        color: #ffeb3b;
        margin-right: 8px;
    }
    .logout-link {
        color: #fff;
        background: #e53935;
        border-radius: 4px;
        padding: 4px 12px;
        text-decoration: none;
        font-size: 15px;
        margin-left: 5px;
        transition: background 0.2s;
    }
    .logout-link:hover {
        background: #b71c1c;
    }
    </style>
    <div class="center-box">
        <h2>TRANG QUẢN TRỊ</h2>
        <ul class="menu-list">
            <li>
                <a class="menu-link" href="quanlyloaitin.php">
                    <span class="menu-icon">📰</span>
                    Quản lý loại tin
                </a>
            </li>
            <li>
                <a class="menu-link" href="quanlytin.php">
                    <span class="menu-icon">🗂️</span>
                    Quản lý tin tức
                </a>
            </li>
            <li>
                <a class="menu-link" href="quanlyuser.php">
                    <span class="menu-icon">👤</span>
                    Quản lý người dùng
                </a>
            </li>
        </ul>
    </div>
</body>
</html>