<?php

// db.php - اتصال به دیتابیس و ذخیره اطلاعات

require_once 'config.php';  // فایل تنظیمات را صدا می‌زنیم

class Database
{
    // این متغیر اتصال به دیتابیس را نگه می‌دارد
    private static $connection = null;

    // این تابع به دیتابیس وصل می‌شود
    public static function connect()
    {
        // اگر قبلا وصل شده بود، دوباره وصل نشو
        if (self::$connection === null) {
            try {
                // دستور اتصال به دیتابیس
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$connection = new PDO($dsn, DB_USER, DB_PASS);

                // تنظیم می‌کنیم که اگر خطایی شد، به ما بگوید
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                echo "✅ اتصال به دیتابیس موفق بود\n";
            } catch (PDOException $e) {
                // اگر نتونست وصل بشه، برنامه را متوقف کن و خطا را نشان بده
                die("❌ خطا در اتصال به دیتابیس: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    // این تابع یک خبر جدید را در دیتابیس ذخیره می‌کند
    public static function saveNews($message_text, $channel_name, $category, $sender_name)
    {
        // اتصال به دیتابیس
        $db = self::connect();

        // دستور SQL برای ذخیره خبر
        $sql = "INSERT INTO news (message_text, channel_name, category, sender_name, created_at) 
                VALUES (:text, :channel, :cat, :sender, NOW())";

        // آماده کردن دستور (برای امنیت)
        $stmt = $db->prepare($sql);

        // اجرای دستور با مقادیر واقعی
        $result = $stmt->execute([
            ':text' => $message_text,
            ':channel' => $channel_name,
            ':cat' => $category,
            ':sender' => $sender_name
        ]);

        return $result;  // اگر موفق بود true برمی‌گرداند
    }

    // این تابع همه اخبار را از دیتابیس می‌خواند
    public static function getAllNews($limit = 100)
    {
        $db = self::connect();
        $sql = "SELECT * FROM news ORDER BY created_at DESC LIMIT " . $limit;
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // این تابع اخبار را بر اساس دسته‌بندی فیلتر می‌کند
    public static function getNewsByCategory($category)
    {
        $db = self::connect();
        $sql = "SELECT * FROM news WHERE category = :cat ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':cat' => $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
