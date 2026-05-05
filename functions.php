<?php

// functions.php - توابع کمکی برای کارهای مختلف

require_once 'config.php';

// تابع 1: تشخیص دسته‌بندی خبر بر اساس کلمات کلیدی
function detectCategory($text)
{
    global $keywords;  // از کلمات کلیدی که در config.php تعریف کردیم استفاده می‌کنیم

    // برای هر دسته‌بندی و کلمات کلیدی آن
    foreach ($keywords as $category => $words) {
        foreach ($words as $word) {
            // اگر کلمه کلیدی در متن وجود داشت
            if (mb_strpos($text, $word) !== false) {
                return $category;  // همان دسته را برمی‌گردانیم
            }
        }
    }

    // اگر هیچ کلمه کلیدی پیدا نشد
    return 'متفرقه';
}

// تابع 2: پاکسازی متن (حذف فاصله‌های اضافی و لینک‌ها)
function cleanText($text)
{
    // حذف فاصله‌های اضافی (چند فاصله را یکی می‌کند)
    $text = preg_replace('/\s+/', ' ', $text);

    // حذف لینک‌ها (اختیاری - می‌خواهی حذف کنی)
    // $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);

    // حذف فاصله از اول و آخر متن
    $text = trim($text);

    return $text;
}

// تابع 3: ارسال پیام به ایتا (برای گزارش به ادمین)
function sendToEitaa($chat_id, $message)
{
    // آدرس API ایتا برای ارسال پیام
    $url = "https://eitaa.com/bot" . EITAA_TOKEN . "/sendMessage";

    // اطلاعاتی که می‌خواهیم بفرستیم
    $data = [
        'chat_id' => $chat_id,
        'text' => $message
    ];

    // با curl درخواست می‌فرستیم به ایتا
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

// تابع 4: دریافت پیام‌های جدید از ایتا
function getEitaaUpdates($last_id = 0)
{
    // آدرس API ایتا برای دریافت آپدیت‌ها
    $url = "https://eitaa.com/bot" . EITAA_TOKEN . "/getUpdates?offset=" . ($last_id + 1);

    // دریافت اطلاعات از ایتا
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // اگر اطلاعات داشت، برگردان
    if ($data && isset($data['result'])) {
        return $data['result'];
    }

    return [];
}
