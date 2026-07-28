<?php
header('Content-Type: application/json; charset=utf-8');

// 檢查請求方式
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => '無效的請求方式'));
    exit;
}

// 取得前端傳來的表單資料並過濾標籤（防止 XSS 攻擊，相容舊版 PHP/DW）
$company = isset($_POST['company']) ? filter_var($_POST['company'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$contact = isset($_POST['contact']) ? filter_var($_POST['contact'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$phone   = isset($_POST['phone'])   ? filter_var($_POST['phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$email   = isset($_POST['email'])   ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : '';
$notes   = isset($_POST['notes'])   ? filter_var($_POST['notes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

// 簡單驗證必填欄位
if (empty($company) || empty($contact) || empty($phone) || !$email || empty($notes)) {
    echo json_encode(array('status' => 'error', 'message' => '請完整填寫所有必填欄位，並確認 Email 格式正確。'));
    exit;
}

// 收件者與郵件內容設定
$to_email = "service@newgreen.com.tw"; // 請確認替換為貴公司實際收件信箱
$subject = "【線上詢價通知】" . $company . " - " . $contact;

$message_body  = "您收到一筆新的線上詢價單：\n\n";
$message_body .= "公司名稱：" . $company . "\n";
$message_body .= "聯絡人：" . $contact . "\n";
$message_body .= "聯絡電話：" . $phone . "\n";
$message_body .= "電子郵件：" . $email . "\n";
$message_body .= "需求說明 / 規格：\n" . $notes . "\n";

// 信件標頭設定
$headers  = "From: no-reply@newgreen.com.tw\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// 發送郵件
if (mail($to_email, $subject, $message_body, $headers)) {
    echo json_encode(array('status' => 'success', 'message' => '詢價單已成功送出！'));
} else {
    echo json_encode(array('status' => 'error', 'message' => '郵件發送失敗，請直接來電與我們聯繫。'));
}
?>