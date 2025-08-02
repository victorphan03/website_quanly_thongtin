<?php
include 'connect.php';
session_start();
// Giả lập user, thực tế bạn lấy từ session đăng nhập
$user = isset($_SESSION['user']) ? $_SESSION['user'] : ["role" => "viewer", "username" => "Khách"];
$role = $user['role'];
$username = $user['username'];


// Chỉ cho phép quyền baiviet hoặc admin
if (!in_array($role, ['baiviet', 'admin'])) {
    echo '<div style="color:red;text-align:center;margin-top:40px;font-size:20px">Bạn không có quyền truy cập chức năng này!</div>';
    exit;
}

// Lấy danh sách loại tin
$loaitin = $conn->query("SELECT * FROM loaitin WHERE trangthai=1 ORDER BY tenloai ASC")->fetch_all(MYSQLI_ASSOC);

// Thêm bài viết
if(isset($_POST['action']) && $_POST['action'] == 'add') {
    $tieude = trim($_POST['tieude']);
    $noidung = trim($_POST['noidung']);
    $loaitin_id = intval($_POST['loaitin_id']);
    if($tieude && $noidung && $loaitin_id) {
        $stmt = $conn->prepare("INSERT INTO baiviet (tieude, noidung, loaitin_id, tacgia) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            echo 'Lỗi prepare: ' . $conn->error;
            exit;
        }
        $stmt->bind_param("ssis", $tieude, $noidung, $loaitin_id, $username);
        $stmt->execute();
        echo 'success';
    } else {
        echo 'empty';
    }
    exit;
}
// Sửa bài viết
if(isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = intval($_POST['id']);
    $tieude = trim($_POST['tieude']);
    $noidung = trim($_POST['noidung']);
    $loaitin_id = intval($_POST['loaitin_id']);
    if($tieude && $noidung && $loaitin_id) {
        $stmt = $conn->prepare("UPDATE baiviet SET tieude=?, noidung=?, loaitin_id=? WHERE id=?");
        $stmt->bind_param("ssii", $tieude, $noidung, $loaitin_id, $id);
        $stmt->execute();
        echo 'success';
    } else {
        echo 'empty';
    }
    exit;
}
// Xóa bài viết
if(isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM baiviet WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo 'success';
    exit;
}
// Lấy danh sách bài viết
$result = $conn->query("SELECT b.*, l.tenloai FROM baiviet b JOIN loaitin l ON b.loaitin_id=l.id ORDER BY b.id DESC");
$baiviet = [];
if ($result) {
    $baiviet = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tin tức</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; }
        .header-top { background: #ffe0b2; padding: 18px 0 5px 0; text-align: center; border-bottom: 1px solid #f7c873; }
        .header-top .icon { display: inline-block; vertical-align: middle; font-size: 28px; margin-right: 10px; }
        .header-top-title { display: inline-block; font-size: 24px; color: #d2691e; font-weight: bold; vertical-align: middle; }
        .header-top-desc { display: block; font-size: 13px; color: #888; margin-top: 2px; }
        /* .navbar { background: #232323; padding: 0; display: flex; align-items: center; justify-content: space-between; }
        .navbar ul { margin: 0; padding: 0 0 0 10px; list-style: none; display: flex; }
        .navbar li { margin-right: 20px; }
        .navbar a { display: inline-block; color: #fff; text-decoration: none; padding: 16px 0; font-size: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .navbar .user { color: #fff; margin-right: 15px; }
        .navbar .logout { color: #fff; background: #0074d9; padding: 6px 13px; border-radius: 3px; margin-right: 15px; text-decoration: none; } */
        
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
        
        .main-content { max-width: 1100px; margin: 25px auto 0 auto; background: #fff; box-shadow: 0 1px 10px #0001; border-radius: 5px; padding: 25px 25px 20px 25px; }
        .main-content h2 { font-size: 26px; color: #1a73e8; margin-bottom: 18px; }
        .title-icon { font-size: 25px; color: #fbc02d; margin-right: 6px; vertical-align: middle; }
        .add-btn { background: #1976d2; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; margin-bottom: 13px; }
        .add-btn:hover { background: #135ba1; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; background: #fff; }
        th, td { border: 1px solid #e0e0e0; padding: 11px 8px; text-align: center; font-size: 15px; }
        th { background: #f5f5f5; color: #444; }
        .btn-sua { background: #ffeb3b; color: #333; border: none; border-radius: 4px; padding: 5px 15px; cursor: pointer; font-size: 15px; font-weight: bold; }
        .btn-xoa { background: #e53935; color: #fff; border: none; border-radius: 4px; padding: 5px 15px; cursor: pointer; font-size: 15px; font-weight: bold; }
        .footer { margin-top: 38px; text-align: center; color: #888; font-size: 15px; }
        @media (max-width: 700px) { .main-content { padding: 6px 3px; } .navbar ul { flex-direction: column; } table, th, td { font-size: 12px; } }
        .btn-sua:hover { background: #fdd835; }
        .btn-xoa:hover { background: #c62828; }
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
            max-width: 400px;
            margin: 100px auto 0 auto;
            padding: 25px 25px 18px 25px;
            box-shadow: 0 2px 16px #0002;
            position: relative;
        }
        /* Modal đẹp cho thêm/sửa bài viết */
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
            max-width: 400px;
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
        .modal-box input[type="text"], .modal-box select, .modal-box textarea {
            width: 100%;
            padding: 9px 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            background: #f7fafd;
            box-sizing: border-box;
        }
        .modal-actions {
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
        .modal-error {
            color: #e53935;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
            display: none;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="header-top">
        <span class="icon">🗂️</span>
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
        <h2><span class="title-icon">🗂️</span> QUẢN LÝ TIN TỨC</h2>
        <button class="add-btn" id="btnShowAddModal">Thêm mới</button>
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tiêu đề</th>
                    <th>Loại tin</th>
                    <th>Ngày tạo</th>
                    <th>Tác giả</th>
                    <th>Sửa</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody id="baivietTable">
                <?php foreach($baiviet as $bv): ?>
                <tr data-id="<?= $bv['id'] ?>">
                    <td><?= $bv['id'] ?></td>
                    <td><?= htmlspecialchars($bv['tieude']) ?></td>
                    <td><?= htmlspecialchars($bv['tenloai']) ?></td>
                    <td><?= $bv['ngaytao'] ?></td>
                    <td><?= htmlspecialchars($bv['tacgia']) ?></td>
                    <td><button class="btn-sua btn-edit" data-id="<?= $bv['id'] ?>">Sửa</button></td>
                    <td><button class="btn-xoa btn-delete" data-id="<?= $bv['id'] ?>">Xóa</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="footer">© 2025 Hệ thống quản lý tin tức.</div>
    <!-- Modal for Add/Edit -->
    <div class="modal-overlay" id="modalBaiViet">
        <div class="modal-box">
            <h3 id="modalTitle">Thêm mới bài viết</h3>
            <div class="modal-error" id="modalError"></div>
            <label for="modalTieuDe">Tiêu đề</label>
            <input type="text" id="modalTieuDe" autocomplete="off">
            <label for="modalLoaiTin">Loại tin</label>
            <select id="modalLoaiTin">
                <option value="">-- Chọn loại tin --</option>
                <?php foreach($loaitin as $lt): ?>
                <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['tenloai']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="modalNoiDung">Nội dung</label>
            <textarea id="modalNoiDung" rows="5" style="width:100%;padding:8px;border-radius:5px;border:1px solid #ccc;font-size:15px;"></textarea>
            <div class="modal-actions">
                <button class="cancel" id="btnCancelModal">Hủy</button>
                <button id="btnSaveModal">Lưu</button>
            </div>
        </div>
    </div>
<script>
$(function() {
    let editId = null;
    function showModal(title, data) {
        $('#modalTitle').text(title);
        $('#modalTieuDe').val(data ? data.tieude : '');
        $('#modalLoaiTin').val(data ? data.loaitin_id : '');
        $('#modalNoiDung').val(data ? data.noidung : '');
        $('#modalError').hide();
        $('#modalBaiViet').fadeIn(120);
        setTimeout(() => { $('#modalTieuDe').focus(); }, 200);
    }
    function hideModal() {
        $('#modalBaiViet').fadeOut(100);
        editId = null;
    }
    $('#btnShowAddModal').click(function() {
        editId = null;
        showModal('Thêm mới bài viết');
    });
    $(document).on('click', '.btn-edit', function() {
        editId = $(this).data('id');
        const row = $(this).closest('tr');
        showModal('Sửa bài viết', {
            tieude: row.find('td').eq(1).text(),
            loaitin_id: row.find('td').eq(2).text(),
            noidung: row.data('noidung') || ''
        });
    });
    $('#btnCancelModal').click(function() { hideModal(); });
    // Thêm/sửa bài viết
    $('#btnSaveModal').click(function() {
        const tieude = $('#modalTieuDe').val().trim();
        const loaitin_id = $('#modalLoaiTin').val();
        const noidung = $('#modalNoiDung').val().trim();
        if (!tieude || !loaitin_id || !noidung) {
            $('#modalError').text('Vui lòng nhập đầy đủ thông tin!').show();
            return;
        }
        let action = editId ? 'edit' : 'add';
        let data = {action, tieude, loaitin_id, noidung};
        if(editId) data.id = editId;
        $.post('quanlytin.php', data, function(res) {
            if(res === 'success') location.reload();
            else $('#modalError').text('Lỗi: ' + res).show();
        });
    });
    // Xóa bài viết
    $(document).on('click', '.btn-delete', function() {
        if(confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
            let id = $(this).data('id');
            $.post('quanlytin.php', {action: 'delete', id}, function(res) {
                if(res === 'success') location.reload();
                else alert('Lỗi: ' + res);
            });
        }
    });
    // Đóng modal khi click ngoài
    $('#modalBaiViet').on('click', function(e) { if(e.target === this) hideModal(); });
});
</script>
</body>
</html>
