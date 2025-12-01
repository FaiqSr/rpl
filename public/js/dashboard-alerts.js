/**
 * Shared Alert System for Dashboard Pages
 * Provides: Pop-up Alerts (only on monitoring page), Browser Push Notifications
 * Note: Sticky Bar removed - only pop-up alerts are used on monitoring page
 */

// ========== POP-UP ALERT SYSTEM ==========

function checkUrgentAlerts(data) {
  const { status, prediction_6h, anomalies, forecast_summary_6h } = data;
  const alerts = [];
  
  // 1. Check status BURUK dengan confidence tinggi
  if (status && status.label === 'buruk' && status.confidence >= 0.6) {
    alerts.push({
      type: 'critical',
      title: '🚨 Peringatan: Kondisi Kandang Membahayakan',
      message: status.message || 'Kondisi lingkungan tidak optimal dan berpotensi membahayakan kesehatan ayam.',
      action: 'Segera lakukan penyesuaian suhu, kelembaban, ventilasi, atau pencahayaan. Jika perlu, hubungi dokter hewan.',
      urgency: 'high'
    });
  }
  
  // 2. Check prediksi 6h menunjukkan BURUK
  if (status && status.probability && status.probability.BURUK > 0.55) {
    alerts.push({
      type: 'warning',
      title: '⚠️ Prediksi: Risiko Meningkat dalam 6 Jam',
      message: `Model ML memprediksi kondisi kandang berpotensi memburuk (${(status.probability.BURUK * 100).toFixed(1)}% kemungkinan BURUK).`,
      action: 'Lakukan tindakan pencegahan: periksa ventilasi, suhu, dan kelembaban.',
      urgency: 'medium'
    });
  }
  
  // 3. Check anomali critical
  if (anomalies && Array.isArray(anomalies)) {
    const criticalAnomalies = anomalies.filter(a => a.severity === 'critical');
    if (criticalAnomalies.length > 0) {
      alerts.push({
        type: 'critical',
        title: `🚨 ${criticalAnomalies.length} Anomali Kritis Terdeteksi`,
        message: criticalAnomalies.slice(0, 3).map(a => a.message || a.type).join(', '),
        action: 'Segera periksa sensor dan kondisi kandang.',
        urgency: 'high'
      });
    }
  }
  
  // 4. Check forecast menunjukkan threshold akan dilampaui
  if (forecast_summary_6h && Array.isArray(forecast_summary_6h)) {
    forecast_summary_6h.forEach(forecast => {
      if (forecast.risk && (forecast.risk.includes('di luar batas aman') || forecast.risk.includes('bahaya'))) {
        alerts.push({
          type: 'warning',
          title: `⚠️ ${forecast.metric} Diprediksi Keluar Batas Aman`,
          message: forecast.summary || `${forecast.metric} diprediksi keluar batas aman dalam 6 jam ke depan.`,
          action: `Periksa dan sesuaikan ${forecast.metric.toLowerCase()} dalam beberapa jam ke depan.`,
          urgency: 'medium'
        });
      }
    });
  }
  
  return alerts;
}

function showUrgentAlert(alert) {
  if (typeof Swal === 'undefined') {
    console.warn('SweetAlert2 not loaded');
    return;
  }
  
  const icon = alert.type === 'critical' ? 'error' : 'warning';
  const confirmButtonColor = alert.type === 'critical' ? '#dc2626' : '#facc15';
  
  Swal.fire({
    icon: icon,
    title: alert.title,
    html: `
      <div style="text-align: left; margin-top: 1rem;">
        <p style="margin-bottom: 0.75rem; font-size: 0.95rem; line-height: 1.6;">${alert.message}</p>
        <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 8px; border-left: 4px solid ${confirmButtonColor};">
          <strong style="color: ${confirmButtonColor}; display: block; margin-bottom: 0.5rem;">Tindakan Disarankan:</strong>
          <p style="margin: 0; font-size: 0.875rem; line-height: 1.5;">${alert.action}</p>
        </div>
      </div>
    `,
    confirmButtonText: 'Saya Mengerti',
    confirmButtonColor: confirmButtonColor,
    allowOutsideClick: false,
    allowEscapeKey: true,
    showCloseButton: true,
    width: '600px'
  });
}

// ========== BROWSER PUSH NOTIFICATION ==========

let notificationPermission = Notification.permission;

async function requestNotificationPermission() {
  if ('Notification' in window && Notification.permission === 'default') {
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
      notificationPermission = 'granted';
      localStorage.setItem('notificationPermission', 'granted');
    }
  }
}

function showBrowserNotification(title, options) {
  if (!('Notification' in window)) {
    return;
  }
  
  if (Notification.permission === 'granted') {
    new Notification(title, {
      icon: '/favicon.ico',
      badge: '/favicon.ico',
      body: options.body || '',
      tag: options.tag || 'chickpatrol-alert',
      requireInteraction: options.requireInteraction || false,
      ...options
    });
  }
}

function notifyUrgentAlert(alert) {
  showBrowserNotification(alert.title, {
    body: alert.message + '\n\n' + alert.action,
    tag: `alert-${alert.type}-${Date.now()}`,
    requireInteraction: alert.urgency === 'high'
  });
}

// ========== MONITORING DATA POLLING ==========

let alertCheckInterval = null;
let lastAlertHash = null;

async function checkMonitoringAlerts() {
  try {
    const res = await fetch('/api/monitoring/tools?t=' + Date.now(), {
      headers: { 'Accept': 'application/json' }
    });
    
    if (!res.ok) {
      console.warn('Failed to fetch monitoring data:', res.status);
      return;
    }
    
    const data = await res.json();
    
    // Check for urgent alerts
    const alerts = checkUrgentAlerts(data);
    
    // Create hash of alerts to avoid duplicate pop-ups
    const alertHash = JSON.stringify(alerts.map(a => a.title + a.type));
    
    if (alerts.length > 0 && alertHash !== lastAlertHash) {
      lastAlertHash = alertHash;
      
      // Show most urgent first
      alerts.sort((a, b) => {
        const urgencyOrder = { 'high': 3, 'medium': 2, 'low': 1 };
        return urgencyOrder[b.urgency] - urgencyOrder[a.urgency];
      });
      
      // Show first alert immediately (pop-up)
      setTimeout(() => {
        showUrgentAlert(alerts[0]);
        notifyUrgentAlert(alerts[0]);
        
        // Queue other alerts
        if (alerts.length > 1) {
          setTimeout(() => {
            alerts.slice(1).forEach((alert, index) => {
              setTimeout(() => {
                showUrgentAlert(alert);
                notifyUrgentAlert(alert);
              }, index * 3000);
            });
          }, 3000);
        }
      }, 1000);
    }
    
    // Sticky bar removed - only pop-up alerts are shown
    
  } catch (error) {
    console.error('Error checking monitoring alerts:', error);
  }
}

function startAlertPolling(intervalSeconds = 30) {
  // Stop existing polling if any
  if (alertCheckInterval) {
    clearInterval(alertCheckInterval);
  }
  
  // Initial check
  checkMonitoringAlerts();
  
  // Poll every intervalSeconds
  alertCheckInterval = setInterval(checkMonitoringAlerts, intervalSeconds * 1000);
}

function stopAlertPolling() {
  if (alertCheckInterval) {
    clearInterval(alertCheckInterval);
    alertCheckInterval = null;
  }
}

// ========== INITIALIZATION ==========

document.addEventListener('DOMContentLoaded', function() {
  const currentPage = window.location.pathname;
  
  // Hanya aktifkan alert visual (pop-up) di halaman monitoring alat
  const isMonitoringPage = currentPage.includes('/monitoring');
  
  if (isMonitoringPage) {
    // Halaman monitoring: aktifkan pop-up alert (sticky bar dihapus)
    console.log('Monitoring page detected - pop-up alerts enabled');
    
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
      setTimeout(() => {
        requestNotificationPermission();
      }, 2000);
    }
    
    // Monitoring page menggunakan loadMonitoring() sendiri yang sudah ada
    // Tidak perlu polling karena sudah ada di loadMonitoring()
  } else {
    // Halaman lain: TIDAK ada alert visual sama sekali
    // Telegram notification tetap berjalan di background via scheduler
    console.log('Non-monitoring page - alerts disabled (Telegram notification runs in background)');
    
    // Request browser notification permission (optional, untuk future use)
    if ('Notification' in window && Notification.permission === 'default') {
      setTimeout(() => {
        requestNotificationPermission();
      }, 2000);
    }
  }
});

