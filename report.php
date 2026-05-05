<?php

// report.php - گرفتن گزارش از اخبار ذخیره شده

require_once 'db.php';

echo "=== آخرین اخبار ذخیره شده ===\n\n";

$news = Database::getAllNews(10);
foreach ($news as $item) {
    echo "📌 [{$item['category']}] {$item['message_text']}\n";
    echo "   کانال: {$item['channel_name']} - زمان: {$item['created_at']}\n";
    echo "-----------------------------------\n";
}

echo "\n=== آمار دسته‌بندی ===\n";
$categories = ['سیاسی', 'اقتصادی', 'ورزشی', 'فناوری', 'متفرقه'];
foreach ($categories as $cat) {
    $items = Database::getNewsByCategory($cat);
    echo "{$cat}: " . count($items) . " خبر\n";
}
