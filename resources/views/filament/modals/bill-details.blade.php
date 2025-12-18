@php
    $totalConsumption = $record->billDetails->sum('consumption');
    $totalAmount = $record->total_amount;
@endphp

<div class="space-y-6">
    <!-- Thông tin hóa đơn -->
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Thông tin hóa đơn</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Mã hóa đơn:</span>
                <div class="font-semibold">HĐ{{ $record->id }}</div>
            </div>
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Kỳ hóa đơn:</span>
                <div>{{ $record->billing_date?->format('m/Y') }}</div>
            </div>
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Ngày đến hạn:</span>
                <div class="{{ $record->due_date < now() ? 'text-red-600' : '' }}">
                    {{ $record->due_date?->format('d/m/Y') }}
                </div>
            </div>
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Trạng thái:</span>
                <div>
                    <span class="px-2 py-1 rounded text-xs font-medium
                        @if($record->status === 'PAID') bg-green-100 text-green-800
                        @elseif($record->status === 'PENDING') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ $record->status === 'PAID' ? 'Đã thanh toán' : ($record->status === 'PENDING' ? 'Chờ thanh toán' : 'Đã hủy') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết tiêu thụ -->
    @if($record->billDetails->count() > 0)
        <div>
            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Chi tiết tiêu thụ</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Công tơ</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tiêu thụ (kWh)</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Trợ cấp (kWh)</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tính tiền (kWh)</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Đơn giá</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($record->billDetails as $detail)
                            <tr>
                                <td class="px-3 py-2 text-sm">
                                    <div class="font-medium">{{ $detail->electricMeter->meter_number ?? 'N/A' }}</div>
                                    <div class="text-gray-500 text-xs">HSN: {{ number_format($detail->hsn ?? 1, 2) }}</div>
                                </td>
                                <td class="px-3 py-2 text-center text-sm">{{ number_format($detail->consumption, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-sm">{{ number_format($detail->subsidized_applied ?? 0, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-sm font-medium">{{ number_format($detail->chargeable_kwh, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-sm">{{ number_format($detail->price_per_kwh, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-sm font-medium">{{ number_format($detail->amount, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tổng kết -->
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Tổng tiêu thụ:</span>
                <div class="text-lg font-bold text-blue-600">{{ number_format($totalConsumption, 0, ',', '.') }} kWh</div>
            </div>
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Tổng tiền:</span>
                <div class="text-lg font-bold text-green-600">{{ number_format($totalAmount, 0, ',', '.') }} đ</div>
            </div>
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-400">Ngày tạo:</span>
                <div>{{ $record->created_at?->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>