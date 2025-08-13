<?php
// 設定上傳目標資料夾
$target_dir = __DIR__ . "/uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$upload_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $tmp_name = $_FILES['file']['tmp_name'];
        $name = basename($_FILES['file']['name']);
        $target_file = $target_dir . $name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $upload_message = "Uploaded to: $target_file";
        } else {
            $upload_message = "Upload failed!";
        }
    } else {
        $upload_message = "No file uploaded!";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>檔案上傳</title>
</head>
<body>
    <h2>上傳 PHP / PHAR 檔案</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">上傳檔案</button>
    </form>

    <?php if ($upload_message): ?>
        <p><?php echo htmlspecialchars($upload_message); ?></p>
    <?php endif; ?>
</body>
</html>
