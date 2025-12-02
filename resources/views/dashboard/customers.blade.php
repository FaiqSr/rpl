<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pelanggan - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
  <!-- Google Fonts - Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <style>
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body { background: #F8F9FB; margin: 0; }
    
    .main-content {
      margin-left: 220px;
      padding: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
      }
      
      .content-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .customer-table {
        font-size: 0.75rem;
      }
      
      .customer-table th,
      .customer-table td {
        padding: 0.75rem 0.5rem;
      }
      
      .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
      }
      
      .btn-action {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
      }
    }
    
    .content-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }
    
    .content-header h1 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
      color: #2F2F2F;
    }
    
    .content-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    
    /* Override global table styles untuk desktop */
    .customer-table {
      width: 100%;
      border-collapse: collapse;
      display: table !important;
    }
    
    .customer-table thead {
      background: #f8f9fa;
      display: table-header-group !important;
    }
    
    .customer-table tbody {
      display: table-row-group !important;
    }
    
    .customer-table tr {
      display: table-row !important;
    }
    
    .customer-table th {
      padding: 1rem;
      text-align: center;
      font-size: 0.875rem;
      font-weight: 600;
      color: #2F2F2F;
      border-bottom: 1px solid #e9ecef;
      display: table-cell !important;
      vertical-align: middle;
    }
    
    .customer-table td {
      padding: 1rem;
      font-size: 0.875rem;
      color: #2F2F2F;
      border-bottom: 1px solid #f8f9fa;
      display: table-cell !important;
      vertical-align: middle;
      text-align: center;
    }
    
    .customer-table td::before {
      display: none !important;
    }
    
    .customer-table tbody tr:hover {
      background: #f8f9fa;
    }
    
    .status-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .status-badge.aktif {
      background: #d1fae5;
      color: #065f46;
    }
    
    .status-badge.nonaktif {
      background: #fee2e2;
      color: #991b1b;
    }
    
    .action-buttons {
      display: flex;
      gap: 0.5rem;
    }
    
    .btn-action {
      padding: 0.375rem 0.75rem;
      border: none;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-view {
      background: #e0f2fe;
      color: #0369a1;
    }
    
    .btn-view:hover {
      background: #bae6fd;
    }
    
    .btn-deactivate {
      background: #fee2e2;
      color: #991b1b;
    }
    
    .btn-deactivate:hover {
      background: #fecaca;
    }
    
    .modal-content {
      border-radius: 8px;
      border: none;
    }
    
    .modal-header {
      border-bottom: 1px solid #e9ecef;
      padding: 1.5rem;
    }
    
    .modal-body {
      padding: 1.5rem;
    }
    
    .detail-row {
      display: flex;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .detail-label {
      font-weight: 600;
      color: #6c757d;
      width: 150px;
      flex-shrink: 0;
    }
    
    .detail-value {
      color: #2F2F2F;
      flex: 1;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <div class="main-content">
    <div class="content-header">
      <h1>Pelanggan</h1>
    </div>
    
    <div class="content-card">
      <table class="customer-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Tanggal Daftar</th>
            <th>Jumlah Pesanan</th>
            <th>Total Pembelian</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="customersTableBody">
          <tr>
            <td colspan="7" class="text-center p-4 text-muted">
              <i class="fa-solid fa-spinner fa-spin"></i> Memuat data...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Customer Detail Modal -->
  <div class="modal fade" id="customerDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Pelanggan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="customerDetailContent">
          <div class="text-center p-4">
            <i class="fa-solid fa-spinner fa-spin"></i> Memuat data...
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    // Load customers
    async function loadCustomers() {
      try {
        const response = await fetch('/api/customers', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        
        if (!response.ok) throw new Error('Failed to load customers');
        
        const customers = await response.json();
        const tbody = document.getElementById('customersTableBody');
        
        if (customers.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center p-4 text-muted">Tidak ada pelanggan</td></tr>';
          return;
        }
        
        tbody.innerHTML = customers.map(customer => {
          const statusClass = customer.status === 'aktif' ? 'aktif' : 'nonaktif';
          const totalPurchase = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
          }).format(customer.total_purchase);
          
          return `
            <tr>
              <td>${escapeHtml(customer.name)}</td>
              <td>${escapeHtml(customer.email)}</td>
              <td>${customer.created_at_formatted}</td>
              <td>${customer.orders_count}</td>
              <td>${totalPurchase}</td>
              <td><span class="status-badge ${statusClass}">${customer.status}</span></td>
              <td>
                <div class="action-buttons">
                  <button class="btn-action btn-view" onclick="viewCustomer('${customer.user_id}')">
                    <i class="fa-solid fa-eye"></i> View
                  </button>
                  <button class="btn-action btn-deactivate" onclick="toggleCustomerStatus('${customer.user_id}', '${customer.status}')">
                    <i class="fa-solid fa-ban"></i> Deactivate
                  </button>
                </div>
              </td>
            </tr>
          `;
        }).join('');
      } catch (error) {
        console.error('Error loading customers:', error);
        document.getElementById('customersTableBody').innerHTML = 
          '<tr><td colspan="7" class="text-center p-4 text-danger">Gagal memuat data pelanggan</td></tr>';
      }
    }
    
    // View customer details
    async function viewCustomer(userId) {
      try {
        const response = await fetch(`/api/customers/${userId}`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        
        if (!response.ok) throw new Error('Failed to load customer details');
        
        const data = await response.json();
        const customer = data.customer;
        const stats = data.stats;
        
        const totalPurchase = new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          minimumFractionDigits: 0
        }).format(stats.total_purchase);
        
        const lastOrderDate = stats.last_order_date 
          ? new Date(stats.last_order_date).toLocaleDateString('id-ID', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })
          : 'Belum ada pesanan';
        
        document.getElementById('customerDetailContent').innerHTML = `
          <div class="detail-row">
            <div class="detail-label">Nama</div>
            <div class="detail-value">${escapeHtml(customer.name || 'Tidak ada nama')}</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Email</div>
            <div class="detail-value">${escapeHtml(customer.email)}</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Tanggal Daftar</div>
            <div class="detail-value">${new Date(customer.created_at).toLocaleDateString('id-ID', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })}</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Jumlah Pesanan</div>
            <div class="detail-value">${stats.total_orders}</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Total Pembelian</div>
            <div class="detail-value">${totalPurchase}</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Pesanan Terakhir</div>
            <div class="detail-value">${lastOrderDate}</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
              <span class="status-badge ${customer.status === 'aktif' ? 'aktif' : 'nonaktif'}">${customer.status || 'aktif'}</span>
            </div>
          </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('customerDetailModal'));
        modal.show();
      } catch (error) {
        console.error('Error loading customer details:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Gagal memuat detail pelanggan'
        });
      }
    }
    
    // Toggle customer status
    async function toggleCustomerStatus(userId, currentStatus) {
      const action = currentStatus === 'aktif' ? 'menonaktifkan' : 'mengaktifkan';
      
      const result = await Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${action} pelanggan ini?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#22C55E',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
      });
      
      if (result.isConfirmed) {
        try {
          const response = await fetch(`/api/customers/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          });
          
          if (!response.ok) throw new Error('Failed to toggle status');
          
          const data = await response.json();
          
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: data.message
          });
          
          loadCustomers();
        } catch (error) {
          console.error('Error toggling status:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal mengubah status pelanggan'
          });
        }
      }
    }
    
    // Helper function
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    // Load on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadCustomers();
    });
  </script>
</body>
</html>

