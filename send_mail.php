<?php
// 設定回傳格式為 JSON
header('Content-Type: application/json; charset=utf-8');

// 僅允許 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '無效的請求方式']);
    exit;
}

// 取得前端傳來的表單資料並過濾標籤（防止 XSS 攻擊）
$company = filter_var($_POST['company'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$contact = filter_var($_POST['contact'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$phone   = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$notes   = filter_var($_POST['notes'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// 簡單驗證必填欄位
if (empty($company) || empty($contact) || empty($phone) || !$email || empty($notes)) {
    echo json_encode(['status' => 'error', 'message' => '請完整填寫所有必填欄位，並確認 Email 格式正確。']);
    exit;
}

/* ==========================================================================
   信件內容組合
   ========================================================================== */
$to_email = "newgreen_co@newgreen.com.tw";
$subject  = "【新懋鋁業官網】收到來自「{$company}」的線上詢價單";

$message_body = "收到新的客戶線上詢價需求：\n\n";
$message_body .= "■ 公司名稱：" . $company . "\n";
$message_body .= "■ 聯 絡 人：" . $contact . "\n";
$message_body .= "■ 聯絡電話：" . $phone . "\n";
$message_body .= "■ 電子郵件：" . $email . "\n\n";
$message_body .= "■ 備註裁切尺寸、數量或公差說明：\n" . $notes . "\n";

/* ==========================================================================
   發信邏輯 (建議設定 SMTP)
   ========================================================================== */

// 方法一：若主機已設定好 sendmail，直接使用 PHP 原生 mail() 函式
$headers  = "From: no-reply@newgreen.com.tw\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to_email, $subject, $message_body, $headers)) {
    echo json_encode(['status' => 'success', 'message' => '詢價單已成功送出！']);
} else {
    echo json_encode(['status' => 'error', 'message' => '郵件發送失敗，請直接來電與我們聯繫。']);
}
?>