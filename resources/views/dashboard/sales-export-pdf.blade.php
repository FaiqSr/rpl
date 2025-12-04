<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan - ChickPatrol</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #22C55E;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2F2F2F;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin: 20px 0;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            color: #22C55E;
            font-size: 16px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #22C55E;
        }
        .summary-item h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #666;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: 600;
            color: #2F2F2F;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background: #22C55E;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background: #E8F5E9;
            color: #22C55E;
        }
        .status-pending {
            background: #FFF3E0;
            color: #FF9800;
        }
        .status-processing {
            background: #E3F2FD;
            color: #2196F3;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Penjualan</h1>
        <p>ChickPatrol Seller Dashboard</p>
        <p>Dibuat pada: {{ $generatedAt }}</p>
        @if($filter !== 'all')
            <p>Filter: {{ ucfirst($filter) }}</p>
        @endif
    </div>
    
    @php
        $totalRevenue = $orders->where('payment_status', 'paid')->sum('total_price');
        $totalOrders = $orders->count();
        $paidOrders = $orders->where('payment_status', 'paid')->count();
    @endphp
    
    <div class="summary">
        <h3>Ringkasan</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <h4>Total Pesanan</h4>
                <div class="value">{{ number_format($totalOrders) }}</div>
            </div>
            <div class="summary-item">
                <h4>Pesanan Lunas</h4>
                <div class="value">{{ number_format($paidOrders) }}</div>
            </div>
            <div class="summary-item">
                <h4>Total Pendapatan</h4>
                <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Tanggal & Waktu (WIB)</th>
                <th>Order ID</th>
                <th>Pembeli</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status</th>
                <th>Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $orderDate = $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i');
                $orderId = substr($order->order_id, 0, 8);
                $statusClass = strtolower($order->status ?? 'pending');
                $paymentClass = strtolower($order->payment_status ?? 'pending');
            @endphp
            @if($order->orderDetail->count() > 0)
                @foreach($order->orderDetail as $index => $detail)
                <tr>
                    @if($index === 0)
                        <td rowspan="{{ $order->orderDetail->count() }}">{{ $orderDate }}</td>
                        <td rowspan="{{ $order->orderDetail->count() }}">{{ $orderId }}</td>
                        <td rowspan="{{ $order->orderDetail->count() }}">{{ $order->buyer_name }}</td>
                    @endif
                    <td>{{ $detail->product->name ?? 'Produk Dihapus' }}</td>
                    <td>{{ $detail->qty }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                    @if($index === 0)
                        <td rowspan="{{ $order->orderDetail->count() }}">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        <td rowspan="{{ $order->orderDetail->count() }}">
                            <span class="status-badge status-{{ $statusClass }}">{{ ucfirst($order->status ?? 'pending') }}</span>
                        </td>
                        <td rowspan="{{ $order->orderDetail->count() }}">
                            <span class="status-badge status-{{ $paymentClass }}">{{ ucfirst($order->payment_status ?? 'pending') }}</span>
                        </td>
                    @endif
                </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $orderDate }}</td>
                    <td>{{ $orderId }}</td>
                    <td>{{ $order->buyer_name }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td>
                        <span class="status-badge status-{{ $statusClass }}">{{ ucfirst($order->status ?? 'pending') }}</span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $paymentClass }}">{{ ucfirst($order->payment_status ?? 'pending') }}</span>
                    </td>
                </tr>
            @endif
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">Tidak ada data penjualan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Total Pesanan: {{ $totalOrders }} | Total Pendapatan: Rp {{ number_format($totalRevenue, 0, ',', '.') }} | Halaman {PAGENO} dari {nbpg}</p>
    </div>
</body>
</html>

