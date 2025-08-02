<?php
include 'connect.php';

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
if ($user['role'] !== 'admin') {
    echo '<div style="color:red;text-align:center;font-size:22px;margin-top:60px;">Bạn không có quyền truy cập chức năng này!</div>';
    exit;
}


// Xử lý thêm mới user
if(isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    echo 'success';
    exit;
}
// Xử lý sửa user
if(isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $sql = "UPDATE users SET username=?, role=?";
    $params = [$username, $role];
    $types = "ss";
    if(!empty($_POST['password'])) {
        $sql .= ", password=?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }
    $sql .= " WHERE id=?";
    $params[] = $id;
    $types .= "i";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    echo 'success';
    exit;
}
// Xử lý xóa user
if(isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo 'success';
    exit;
}
// Lấy danh sách user
$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý User</title>
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
        .main-content { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 16px #0002; padding: 30px 30px 20px 30px; }
        h2 { color: #1a73e8; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { border: 1px solid #e0e0e0; padding: 10px 8px; text-align: center; font-size: 15px; }
        th { background: #f5f5f5; color: #444; }
        .btn { border: none; border-radius: 4px; padding: 5px 15px; cursor: pointer; font-size: 15px; font-weight: bold; }
        .btn-edit { background: #ffeb3b; color: #333; }
        .btn-delete { background: #e53935; color: #fff; }
        .btn-add { background: #1976d2; color: #fff; margin-bottom: 15px; padding: 8px 16px; font-size: 15px; }
        .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); }
        .modal-box { background: #fff; border-radius: 8px; max-width: 350px; margin: 100px auto 0 auto; padding: 25px 25px 18px 25px; box-shadow: 0 2px 16px #0002; position: relative; }
        .modal-box h3 { margin: 0 0 18px 0; color: #1a73e8; font-size: 20px; text-align: center; }
        .modal-box label { display: block; margin-bottom: 7px; color: #444; font-size: 15px; }
        .modal-box input, .modal-box select { width: 100%; padding: 9px 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px; background: #f7fafd; }
        .modal-actions { text-align: right; }
        .modal-box button { background: #1976d2; color: #fff; border: none; border-radius: 4px; padding: 7px 16px; font-size: 15px; cursor: pointer; margin-left: 8px; }
        .modal-box button.cancel { background: #e0e0e0; color: #444; }
        .modal-error { color: #e53935; font-size: 14px; margin-bottom: 10px; text-align: center; display: none; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
<div class="main-content">
    <h2>Quản lý User</h2>
    <button class="btn btn-add" id="btnShowAddModal">Thêm mới User</button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Quyền</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </thead>
        <tbody id="userTable">
            <?php foreach($users as $u): ?>
            <tr data-id="<?= $u['id'] ?>">
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= $u['role'] ?></td>
                <td><button class="btn btn-edit" data-id="<?= $u['id'] ?>" data-username="<?= htmlspecialchars($u['username']) ?>" data-role="<?= $u['role'] ?>">Sửa</button></td>
                <td><button class="btn btn-delete" data-id="<?= $u['id'] ?>">Xóa</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal-overlay" id="modalUser">
    <div class="modal-box">
        <h3 id="modalTitle">Thêm mới User</h3>
        <div class="modal-error" id="modalError"></div>
        <form id="userForm" autocomplete="off">
            <input type="hidden" id="userId">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" required>
            <label for="password">Mật khẩu</label>
            <input type="password" id="password">
            <label for="role">Quyền</label>
            <select id="role">
                <option value="viewer">Chỉ xem</option>
                <option value="baiviet">Quản lý bài viết</option>
                <option value="loaitin">Quản lý loại tin</option>
                <option value="admin">Quản trị</option>
            </select>
            <div class="modal-actions">
                <button type="button" class="cancel" id="btnCancelModal">Hủy</button>
                <button type="submit" id="btnSaveModal">Lưu</button>
            </div>
        </form>
    </div>
</div>
<script>
$(function() {
    function showModal(title, user) {
        $('#modalTitle').text(title);
        $('#modalError').hide();
        if(user) {
            $('#userId').val(user.id);
            $('#username').val(user.username);
            $('#role').val(user.role);
            $('#password').val('');
        } else {
            $('#userId').val('');
            $('#username').val('');
            $('#role').val('viewer');
            $('#password').val('');
        }
        $('#modalUser').fadeIn(120);
        setTimeout(() => { $('#username').focus(); }, 200);
    }
    function hideModal() {
        $('#modalUser').fadeOut(100);
    }
    $('#btnShowAddModal').click(function() {
        showModal('Thêm mới User');
    });
    $(document).on('click', '.btn-edit', function() {
        showModal('Sửa User', {
            id: $(this).data('id'),
            username: $(this).data('username'),
            role: $(this).data('role')
        });
    });
    $('#btnCancelModal').click(function() { hideModal(); });
    // Thêm/sửa user
    $('#userForm').submit(function(e) {
        e.preventDefault();
        var id = $('#userId').val();
        var username = $('#username').val().trim();
        var password = $('#password').val();
        var role = $('#role').val();
        if(!username) {
            $('#modalError').text('Vui lòng nhập tên đăng nhập!').show(); return;
        }
        var action = id ? 'edit' : 'add';
        $.post('quanlyuser.php', {action, id, username, password, role}, function(res) {
            if(res === 'success') location.reload();
            else $('#modalError').text('Lỗi: ' + res).show();
        });
    });
    // Xóa user
    $(document).on('click', '.btn-delete', function() {
        if(confirm('Bạn có chắc chắn muốn xóa user này?')) {
            var id = $(this).data('id');
            $.post('quanlyuser.php', {action: 'delete', id}, function(res) {
                if(res === 'success') location.reload();
                else alert('Lỗi: ' + res);
            });
        }
    });
    // Đóng modal khi click ngoài
    $('#modalUser').on('click', function(e) { if(e.target === this) hideModal(); });
});
</script>
</body>
</html>
