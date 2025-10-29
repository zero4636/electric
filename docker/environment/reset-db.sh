#!/bin/bash

echo "🔄 Bắt đầu reset và seed database..."
echo ""

# Migrate fresh (xóa toàn bộ và tạo lại)
echo "📦 Chạy migrations..."
php artisan migrate:fresh --force

if [ $? -ne 0 ]; then
    echo "❌ Migration thất bại!"
    exit 1
fi

echo "✅ Migration hoàn tất!"
echo ""

# Seed data
echo "🌱 Seed dữ liệu..."
php artisan db:seed --force

if [ $? -ne 0 ]; then
    echo "❌ Seeding thất bại!"
    exit 1
fi

echo ""
echo "✨ Hoàn tất! Database đã được reset và seed dữ liệu thực tế."
echo ""
echo "📊 Thông tin đăng nhập:"
echo "   Email: admin@example.com"
echo "   Password: password"
echo ""
