<?php
// 1. 設定您的 Google reCAPTCHA 密鑰 (Secret Key)
$secretKey = "6Lc_gV8tAAAAAKsb3wFyknLI__fqrFVicZBFjEIb"; // ⚠️ 請將這裡替換為 Google 給您的「密鑰」

// 2. 接收來自前端表單的 reCAPTCHA 回應碼與使用者資料
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
$name    = $_POST['name'] ?? '';
$phone   = $_POST['phone'] ?? '';
$email   = $_POST['email'] ?? '';
$branch  = $_POST['branch'] ?? '';
$message = $_POST['message'] ?? '';

// 3. 檢查使用者是否有勾選「我不是機器人」
if (empty($recaptchaResponse)) {
    echo "<script>alert('請勾選「我不是機器人」驗證！'); history.back();</script>";
    exit;
}

// 4. 向 Google 伺服器發送驗證請求
$verifyUrl = "https://www.google.com/recaptcha/api/siteverify";
$data = [
    'secret'   => $secretKey,
    'response' => $recaptchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($verifyUrl, false, $context);
$responseData = json_decode($response, true);

// 5. 判斷 Google 驗證結果
if (!$responseData['success']) {
    // 驗證失敗 (可能是機器人或密鑰填錯)
    echo "<script>alert('機器人驗證失敗，請再試一次。'); history.back();</script>";
    exit;
}

// --------------------------------------------------
// 6. 驗證成功！開始執行寄信邏輯 (Send Mail)
// --------------------------------------------------

$to = "newgreen_co@newgreen.com.tw"; // 收件者信箱 (公司信箱)
$subject = "【官網線上詢價】來自 " . $name . " 的諮詢需求";

// 組合信件內容
$mailContent = "收到來自官網的線上詢價需求：\n\n";
$mailContent .= "公司 / 聯絡人： " . $name . "\n";
$mailContent .= "聯絡電話： " . $phone . "\n";
$mailContent .= "電子郵件： " . $email . "\n";
$mailContent .= "需求廠區： " . $branch . "\n";
$mailContent .= "詢價與規格內容：\n" . $message . "\n";

$headers = "From: " . $email . "\r\n" .
           "Reply-To: " . $email . "\r\n" .
           "X-Mailer: PHP/" . phpversion();

// 發送信件
if (mail($to, $subject, $mailContent, $headers)) {
    echo "<script>alert('感謝您的詢價！我們已收到您的訊息，將會盡快與您聯繫。'); location.href='index.html';</script>";
} else {
    echo "<script>alert('信件發送失敗，請直接撥打電話 04-23501420 與我們聯繫。'); history.back();</script>";
}
?>