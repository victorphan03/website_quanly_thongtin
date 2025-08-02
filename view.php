<?php
include 'connect.php';

session_start();
// Giả lập user, thực tế bạn lấy từ session đăng nhập
$user = isset($_SESSION['user']) ? $_SESSION['user'] : ["role" => "viewer", "username" => "Khách"];
$role = $user['role'];
$username = $user['username'];

$user = $_SESSION['user'];
if (!in_array($role, ['baiviet', 'admin', 'loaitin', 'viewer'])) {
    echo '<div style="color:red;text-align:center;margin-top:40px;font-size:20px">Bạn không có quyền truy cập chức năng này!</div>';
    exit;
}

// Lấy danh sách tin tức
$result = $conn->query("SELECT b.id, b.tieude, b.noidung, b.ngaytao, l.tenloai, b.tacgia FROM baiviet b JOIN loaitin l ON b.loaitin_id = l.id WHERE l.trangthai=1 ORDER BY b.id DESC");
$baiviet = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tin tức</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; }
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
        .news-list {
            max-width: 800px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 16px #0002;
            padding: 30px 30px 20px 30px;
        }
        .news-item {
            border-bottom: 1px solid #eee;
            padding: 18px 0 10px 0;
        }
        .news-title {
            font-size: 20px;
            color: #1a73e8;
            margin: 0 0 6px 0;
        }
        .news-meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 7px;
        }
        .news-content {
            font-size: 15px;
            color: #333;
        }

        .navbar {
            background: #222;
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

    <div class="news-list">
        <h2 style="color:#1a73e8;text-align:center;margin-bottom:24px;">TIN TỨC MỚI NHẤT</h2>
        <?php if (empty($baiviet)): ?>
            <div style="text-align:center;color:#888;">Chưa có tin tức nào.</div>
        <?php else: ?>
            <?php foreach ($baiviet as $bv): ?>
                <div class="news-item">
                    <div class="news-title">
                        <a href="page.php?id=<?= $bv['id'] ?>" style="color:#1a73e8;text-decoration:none;">
                            <?= htmlspecialchars($bv['tieude']) ?>
                        </a>
                    </div>
                    <div class="news-meta">
                        Loại tin: <?= htmlspecialchars($bv['tenloai']) ?> |
                        Ngày đăng: <?= date('d/m/Y H:i', strtotime($bv['ngaytao'])) ?> |
                        Tác giả: <?= htmlspecialchars($bv['tacgia']) ?>
                    </div>
                    <div class="news-content">
                        <?php
                            $preview = mb_substr(strip_tags($bv['noidung']), 0, 200, 'UTF-8');
                            if (mb_strlen(strip_tags($bv['noidung']), 'UTF-8') > 200) {
                                $preview .= '...';
                            }
                            echo htmlspecialchars($preview);
                        ?>
                    </div>
                </div>
<?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>