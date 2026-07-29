<?php
header('Content-Type: application/json; charset=utf-8');

// 1. 請在此處填入您申請到的 Google reCAPTCHA 「密鑰」(Secret Key)
$recaptcha_secret = '6LcUrGotAAAAACn1WRoYO02DzigueFZ87FOrHBul';

// 檢查請求方式
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => '無效的請求方式'));
    exit;
}

// 2. 驗證 Google reCAPTCHA 機器人驗證碼
$recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

if (empty($recaptcha_response)) {
    echo json_encode(array('status' => 'error', 'message' => '請先完成「我不是機器人」驗證！'));
    exit;
}

// 向 Google 伺服器進行密鑰發送與雙重確認
$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
$verify_response = file_get_contents($verify_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
$response_data = json_decode($verify_response);

if (!$response_data || !$response_data->success) {
    echo json_encode(array('status' => 'error', 'message' => '機器人驗證失敗，請重新勾選後再試！'));
    exit;
}

// 3. 取得前端傳來的表單資料並過濾標籤
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
$to_email = "service@newgreen.com.tw"; // 貴公司實際收件信箱
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