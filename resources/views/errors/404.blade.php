<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang không tồn tại</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-auto bg-white shadow-lg rounded-lg p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.562"/>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-4">404</h1>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Trang không tồn tại</h2>
                
                <p class="text-gray-600 mb-8">
                    {{ $message ?? 'Trang hoặc tài nguyên bạn đang tìm kiếm không tồn tại.' }}
                </p>
                
                <div class="space-y-4">
                    <a href="{{ url('/admin') }}" 
                       class="w-full inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Về trang chủ Admin
                    </a>
                    
                    <button onclick="history.back()" 
                            class="w-full inline-flex justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Quay lại
                    </button>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        Kiểm tra lại địa chỉ URL hoặc liên hệ quản trị viên nếu cần hỗ trợ.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>