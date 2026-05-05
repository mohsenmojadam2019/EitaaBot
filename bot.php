<?php

// bot.php - ربات اصلی که پیام‌ها را می‌خواند و ذخیره می‌کند

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

// کلاس اصلی ربات
class EitaaBot
{
    private $last_update_id = 0;  // آخرین پیامی که خوانده شد

    // تابع اصلی که ربات را اجرا می‌کند
    public function run()
    {
        echo "🤖 ربات ایتا شروع به کار کرد...\n";
        echo "منتظر پیام‌های جدید...\n\n";

        // حلقه بی‌نهایت - ربات همیشه در حال اجراست
        while (true) {
            // گرفتن پیام‌های جدید از ایتا
            $updates = getEitaaUpdates($this->last_update_id);

            // اگر پیام جدید داشت
            if (!empty($updates)) {
                foreach ($updates as $update) {
                    // اگر پیام بود (نه ویرایش یا چیز دیگر)
                    if (isset($update['message'])) {
                        $this->processMessage($update['message']);
                        $this->last_update_id = $update['update_id'];
                    }
                }
            }

            // ۲ ثانیه صبر کن تا دوباره چک کند (فشار به سرور نیاد)
            sleep(2);
        }
    }

    // تابع پردازش یک پیام
    private function processMessage($message)
    {
        // استخراج اطلاعات از پیام
        $message_text = $message['text'] ?? '';           // متن پیام
        $chat_id = $message['chat']['id'] ?? '';          // آیدی کانال/گروه
        $chat_name = $message['chat']['title'] ?? $message['chat']['username'] ?? 'Unknown';
        $sender_name = $message['from']['first_name'] ?? $message['from']['username'] ?? 'Unknown';

        // اگر پیام متنی نداشت (مثل عکس)، نادیده بگیر
        if (empty($message_text)) {
            echo "⚠️ پیام بدون متن دریافت شد (احتمالا عکس یا ویدیو)\n";
            return;
        }

        // پاکسازی متن
        $clean_text = cleanText($message_text);

        // تشخیص دسته‌بندی
        $category = detectCategory($clean_text);

        // نمایش در ترمینال
        echo "📨 پیام جدید از: $chat_name\n";
        echo "📝 متن: " . mb_substr($clean_text, 0, 50) . "...\n";
        echo "📂 دسته: $category\n";
        echo "👤 فرستنده: $sender_name\n";
        echo "-----------------------------------\n";

        // ذخیره در دیتابیس
        $result = Database::saveNews($clean_text, $chat_name, $category, $sender_name);

        if ($result) {
            echo "✅ خبر در دیتابیس ذخیره شد\n\n";

            // اگر خبر مهم بود (دسته‌بندی داشت) به ادمین اطلاع بده
            if ($category != 'متفرقه') {
                $report = "🔔 خبر جدید در دسته $category:\n$clean_text";
                sendToEitaa('ADMIN_CHAT_ID', $report);  // ADMIN_CHAT_ID رو با آیدی خودت عوض کن
            }
        } else {
            echo "❌ خطا در ذخیره خبر\n\n";
        }
    }
}

// اجرای ربات (فقط در ترمینال اجرا شود)
if (php_sapi_name() === 'cli') {
    $bot = new EitaaBot();
    $bot->run();
} else {
    echo "این ربات باید در ترمینال اجرا شود: php bot.php";
}
