<?php
include 'connect.php';
include 'connect.php';

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
// Giả lập user, thực tế bạn lấy từ session đăng nhập
$user = isset($_SESSION['user']) ? $_SESSION['user'] : ["role" => "viewer", "username" => "Khách"];
$role = $user['role'];
$username = $user['username'];
if (!in_array($role, ['admin', 'loaitin'])) {
    echo '<div style="color:red;text-align:center;font-size:22px;margin-top:60px;">Bạn không có quyền truy cập chức năng này!</div>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

// Thêm mới loại tin
if(isset($_POST['action']) && $_POST['action'] == 'add') {
    $tenloai = trim($_POST['tenloai']);
    if($tenloai !== '') {
        $stmt = $conn->prepare("INSERT INTO loaitin (tenloai, trangthai) VALUES (?, 1)");
        $stmt->bind_param("s", $tenloai);
        $stmt->execute();
        echo 'success';
    } else {
        echo 'empty';
    }
    exit;
}
// Sửa loại tin
if(isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = intval($_POST['id']);
    $tenloai = trim($_POST['tenloai']);
    if($tenloai !== '') {
        $stmt = $conn->prepare("UPDATE loaitin SET tenloai=? WHERE id=?");
        $stmt->bind_param("si", $tenloai, $id);
        $stmt->execute();
        echo 'success';
    } else {
        echo 'empty';
    }
    exit;
}
// Xóa loại tin
if(isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM loaitin WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo 'success';
    exit;
}
// Đổi trạng thái
if(isset($_POST['action']) && $_POST['action'] == 'toggle') {
    $id = intval($_POST['id']);
    $trangthai = intval($_POST['trangthai']);
    $stmt = $conn->prepare("UPDATE loaitin SET trangthai=? WHERE id=?");
    $stmt->bind_param("ii", $trangthai, $id);
    $stmt->execute();
    echo 'success';
    exit;
}
// Lấy danh sách loại tin
$result = $conn->query("SELECT * FROM loaitin ORDER BY id ASC");
$loaitin = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý loại tin</title>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3);
        }
        .modal-box {
            background: #fff;
            border-radius: 8px;
            max-width: 350px;
            margin: 100px auto 0 auto;
            padding: 25px 25px 18px 25px;
            box-shadow: 0 2px 16px #0002;
            position: relative;
        }
        .modal-box h3 {
            margin: 0 0 18px 0;
            color: #1a73e8;
            font-size: 20px;
            text-align: center;
        }
        .modal-box label {
            display: block;
            margin-bottom: 7px;
            color: #444;
            font-size: 15px;
        }
        .modal-box input[type="text"] {
            width: 100%;
            padding: 9px 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            background: #f7fafd;
        }
        .modal-box .modal-actions {
            text-align: right;
        }
        .modal-box button {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 7px 16px;
            font-size: 15px;
            cursor: pointer;
            margin-left: 8px;
        }
        .modal-box button.cancel {
            background: #e0e0e0;
            color: #444;
        }
        .modal-box .modal-error {
            color: #e53935;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
            display: none;
        }
        body {
            font-family: Arial, sans-serif;
            background: #fff;
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
        .main-content {
            max-width: 1100px;
            margin: 25px auto 0 auto;
            background: #fff;
            box-shadow: 0 1px 10px #0001;
            border-radius: 5px;
            padding: 25px 25px 20px 25px;
        }
        .main-content h2 {
            font-size: 26px;
            color: #1a73e8;
            margin-bottom: 18px;
        }
        .title-icon {
            font-size: 25px;
            color: #fbc02d;
            margin-right: 6px;
            vertical-align: middle;
        }
        .add-btn {
            background: #1976d2;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            margin-bottom: 13px;
        }
        .add-btn:hover {
            background: #135ba1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            background: #fff;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 11px 8px;
            text-align: center;
            font-size: 15px;
        }
        th {
            background: #f5f5f5;
            color: #444;
        }
        .btn-sua {
            background: #ffeb3b;
            color: #333;
            border: none;
            border-radius: 4px;
            padding: 5px 15px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }
        .btn-xoa {
            background: #e53935;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 5px 15px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }
        .footer {
            margin-top: 38px;
            text-align: center;
            color: #888;
            font-size: 15px;
        }
        @media (max-width: 700px) {
            .main-content { padding: 6px 3px; }
            .navbar ul { flex-direction: column; }
            table, th, td { font-size: 12px; }
        }
        .btn-sua:hover {
            background: #fdd835;
        }
        .btn-xoa:hover {
            background: #c62828;
        }
        .status-enabled {
            background: #ffeb3b;
            color: #333;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 13px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Header và Navbar giữ nguyên như file HTML -->
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
    <div class="main-content">
        <h2><span class="title-icon">📁</span> QUẢN LÝ LOẠI TIN</h2>
        <button class="add-btn" id="btnShowAddModal">Thêm mới</button>
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên loại</th>
                    <th>Trạng thái</th>
                    <th>Sửa</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody id="loaitinTable">
                <?php foreach($loaitin as $lt): ?>
                <tr data-id="<?= $lt['id'] ?>">
                    <td><?= $lt['id'] ?></td>
                    <td><?= htmlspecialchars($lt['tenloai']) ?></td>
                    <td><span class="status-enabled" style="background:<?= $lt['trangthai']?'#ffeb3b':'#f44336' ?>;color:<?= $lt['trangthai']?'#333':'#fff' ?>;cursor:pointer;" data-status="<?= $lt['trangthai'] ?>"><?= $lt['trangthai'] ? 'Bật' : 'Tắt' ?></span></td>
                    <td><button class="btn-sua btn-edit" data-id="<?= $lt['id'] ?>">Sửa</button></td>
                    <td><button class="btn-xoa btn-delete" data-id="<?= $lt['id'] ?>">Xóa</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="footer">© 2025 Hệ thống quản lý tin tức.</div>
    <!-- Modal for Add/Edit -->
    <div class="modal-overlay" id="modalLoaiTin">
        <div class="modal-box">
            <h3 id="modalTitle">Thêm mới loại tin</h3>
            <div class="modal-error" id="modalError"></div>
            <label for="modalTenLoai">Tên loại tin</label>
            <input type="text" id="modalTenLoai" autocomplete="off">
            <div class="modal-actions">
                <button class="cancel" id="btnCancelModal">Hủy</button>
                <button id="btnSaveModal">Lưu</button>
            </div>
        </div>
    </div>
<script>
$(function() {
    let editId = null;
    function showModal(title, value) {
        $('#modalTitle').text(title);
        $('#modalTenLoai').val(value || '');
        $('#modalError').hide();
        $('#modalLoaiTin').fadeIn(120);
        setTimeout(() => { $('#modalTenLoai').focus(); }, 200);
    }
    function hideModal() {
        $('#modalLoaiTin').fadeOut(100);
        editId = null;
    }
    $('#btnShowAddModal').click(function() {
        editId = null;
        showModal('Thêm mới loại tin', '');
    });
    $(document).on('click', '.btn-edit', function() {
        editId = $(this).data('id');
        const row = $(this).closest('tr');
        const tenLoai = row.find('td').eq(1).text();
        showModal('Sửa loại tin', tenLoai);
    });
    $('#btnCancelModal').click(function() { hideModal(); });
    // Thêm/sửa loại tin
    $('#btnSaveModal').click(function() {
        const tenLoai = $('#modalTenLoai').val().trim();
        if (!tenLoai) {
            $('#modalError').text('Vui lòng nhập tên loại tin!').show();
            $('#modalTenLoai').focus();
            return;
        }
        let action = editId ? 'edit' : 'add';
        let data = {action, tenloai: tenLoai};
        if(editId) data.id = editId;
        $.post('quanlyloaitin.php', data, function(res) {
            if(res === 'success') location.reload();
            else $('#modalError').text('Lỗi: ' + res).show();
        });
    });
    // Xóa loại tin
    $(document).on('click', '.btn-delete', function() {
        if(confirm('Bạn có chắc chắn muốn xóa loại tin này?')) {
            let id = $(this).data('id');
            $.post('quanlyloaitin.php', {action: 'delete', id}, function(res) {
                if(res === 'success') location.reload();
                else alert('Lỗi: ' + res);
            });
        }
    });
    // Đổi trạng thái
    $(document).on('click', '.status-enabled', function() {
        let id = $(this).closest('tr').data('id');
        let curr = $(this).data('status');
        let newStatus = curr ? 0 : 1;
        $.post('quanlyloaitin.php', {action: 'toggle', id, trangthai: newStatus}, function(res) {
            if(res === 'success') location.reload();
        });
    });
    // Đóng modal khi click ngoài
    $('#modalLoaiTin').on('click', function(e) { if(e.target === this) hideModal(); });
});
</script>
</body>
</html>
