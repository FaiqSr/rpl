<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ulasan Produk - ChickPatrol</title>
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
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .rating {
            color: #FFC107;
            font-weight: bold;
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
        <h1>Laporan Ulasan Produk</h1>
        <p>ChickPatrol Seller Dashboard</p>
        <p>Dibuat pada: {{ $generatedAt ?? now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB' }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Pelanggan</th>
                <th>Rating</th>
                <th>Ulasan</th>
                <th>Status</th>
                <th>Balasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td>{{ $review->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</td>
                <td>{{ $review->product->name ?? 'Produk Dihapus' }}</td>
                <td>{{ $review->user->name ?? 'User' }}</td>
                <td class="rating">{{ $review->rating }}/5</td>
                <td>{{ \Illuminate\Support\Str::limit($review->review ?? '-', 50) }}</td>
                <td>{{ $review->replies->count() > 0 ? 'Sudah Dibalas' : 'Belum Dibalas' }}</td>
                <td>{{ $review->replies->count() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data ulasan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Total Ulasan: {{ $reviews->count() }} | Halaman {PAGENO} dari {nbpg}</p>
    </div>
</body>
</html>

