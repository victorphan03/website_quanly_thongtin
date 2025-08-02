<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user'])) {
    echo '<div style="color:red;text-align:center;margin-top:40px;font-size:20px">Bạn không có quyền truy cập chức năng này!</div>';
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo '<div style="color:red;text-align:center;margin-top:40px;font-size:20px">Bài viết không tồn tại!</div>';
    exit;
}

$stmt = $conn->prepare("SELECT b.tieude, b.noidung, b.ngaytao, l.tenloai, b.tacgia FROM baiviet b JOIN loaitin l ON b.loaitin_id = l.id WHERE b.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    echo '<div style="color:red;text-align:center;margin-top:40px;font-size:20px">Bài viết không tồn tại!</div>';
    exit;
}
$stmt->bind_result($tieude, $noidung, $ngaytao, $tenloai, $tacgia);
$stmt->fetch();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tieude) ?></title>
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
        .news-detail {
            max-width: 800px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 16px #0002;
            padding: 30px 30px 20px 30px;
        }
        .news-title {
            font-size: 24px;
            color: #1a73e8;
            margin: 0 0 10px 0;
        }
        .news-meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 12px;
        }
        .news-content {
            font-size: 16px;
            color: #333;
            line-height: 1.7;
        }
        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #1976d2;
            text-decoration: none;
            font-size: 15px;
        }
        .back-link:hover {
            text-decoration: underline;
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
    <div class="news-detail">
        <div class="news-title"><?= htmlspecialchars($tieude) ?></div>
        <div class="news-meta">
            Loại tin: <?= htmlspecialchars($tenloai) ?> |
            Ngày đăng: <?= date('d/m/Y H:i', strtotime($ngaytao)) ?> |
            Tác giả: <?= htmlspecialchars($tacgia) ?>
        </div>
        <div class="news-content"><?= nl2br(htmlspecialchars($noidung)) ?></div>
        <a href="view.php" class="back-link">&larr; Quay lại danh sách</a>
    </div>
</body>
</html>