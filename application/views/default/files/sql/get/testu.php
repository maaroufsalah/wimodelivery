<?php
global $con;
// استخرج المستخدمين الذين كلمات سرهم غير مشفرة
$sql = "SELECT user_id, user_pass FROM users WHERE LEFT(user_pass, 4) != '$2y$'";
$stmt = $con->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['user_id'];
    $plain_pass = $row['user_pass'];

    // شفر كلمة السر بـ bcrypt
    $bcrypt_pass = password_hash($plain_pass, PASSWORD_BCRYPT);

    // حدثها في قاعدة البيانات
    $update = $con->prepare("UPDATE users SET user_pass = :pass WHERE user_id = :id");
    $update->execute([
        ':pass' => $bcrypt_pass,
        ':id' => $id
    ]);

    echo "Updated user_id $id ✅\n";
}



