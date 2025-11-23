"""
Test script untuk memverifikasi hasil ML service sesuai dengan contoh dari Jupyter notebook
"""
import requests
import json

# Data sesuai contoh dari Jupyter notebook
# Input: Amonia: 22.5 ppm, Suhu: 29.5 °C, Kelembaban: 62.0 %, Cahaya: 250.0 lux
# Expected: Status PERHATIAN (100%), Anomali: ANOMALI, Prediksi: Amonia: 14.87, Suhu: 30.72, Kelembaban: 50.10, Cahaya: 337.83

# Generate history data (minimal 30 data points)
history = []
base_values = {
    'ammonia': 22.5,
    'temperature': 29.5,
    'humidity': 62.0,
    'light': 250.0
}

# Generate 30 data points dengan variasi kecil
for i in range(30):
    history.append({
        'time': f'2025-11-22 {i:02d}:00',
        'ammonia': base_values['ammonia'] - (i * 0.1),
        'temperature': base_values['temperature'] - (i * 0.1),
        'humidity': base_values['humidity'] - (i * 0.1),
        'light': base_values['light'] - (i * 0.5)
    })

# Pastikan data terakhir sesuai dengan contoh
history[-1] = {
    'time': '2025-11-22 23:00',
    'ammonia': 22.5,
    'temperature': 29.5,
    'humidity': 62.0,
    'light': 250.0
}

# Test ML Service
url = 'http://localhost:5000/predict'
payload = {
    'history': history
}

print("=" * 70)
print("TEST ML SERVICE - Contoh dari Jupyter Notebook")
print("=" * 70)
print(f"\nData Sensor Saat Ini:")
print(f"  Amonia: {history[-1]['ammonia']} ppm")
print(f"  Suhu: {history[-1]['temperature']} °C")
print(f"  Kelembaban: {history[-1]['humidity']} %")
print(f"  Cahaya: {history[-1]['light']} lux")
print(f"\nMengirim request ke ML Service...")

try:
    response = requests.post(url, json=payload, timeout=30)
    
    if response.status_code == 200:
        data = response.json()
        
        print("\nResponse dari ML Service:")
        status_obj = data.get('status', {})
        status_label = status_obj.get('label', 'N/A')
        print(f"\nStatus Kandang: {status_label}")
        
        # Check if we have probability in status_result (from ML service internal)
        # The status object might have different structure
        if 'probability' in status_obj:
            prob = status_obj['probability']
            print(f"   Probabilitas:")
            print(f"     PERHATIAN: {prob.get('PERHATIAN', 0) * 100:.1f}%")
            print(f"     BURUK: {prob.get('BURUK', 0) * 100:.1f}%")
            print(f"     BAIK: {prob.get('BAIK', 0) * 100:.1f}%")
        
        # Check anomalies
        if 'anomalies' in data and len(data['anomalies']) > 0:
            latest_anomaly = data['anomalies'][-1]
            print(f"\nDeteksi Anomali: {latest_anomaly.get('status', 'N/A')}")
            if 'anomaly_score' in latest_anomaly:
                print(f"   Anomaly Score: {latest_anomaly['anomaly_score']:.4f}")
        
        # Check prediction (next sensor value)
        if 'prediction_6h' in data:
            pred_6h = data['prediction_6h']
            if isinstance(pred_6h, dict) and 'ammonia' in pred_6h:
                # Format: {'ammonia': [val1, val2, ...], 'temperature': [...], ...}
                next_ammonia = pred_6h['ammonia'][0] if len(pred_6h['ammonia']) > 0 else 0
                next_temp = pred_6h['temperature'][0] if len(pred_6h['temperature']) > 0 else 0
                next_humidity = pred_6h['humidity'][0] if len(pred_6h['humidity']) > 0 else 0
                next_light = pred_6h['light'][0] if len(pred_6h['light']) > 0 else 0
            elif isinstance(pred_6h, list) and len(pred_6h) > 0:
                # Format: [{'ammonia': val, 'temperature': val, ...}, ...]
                next_pred = pred_6h[0]
                next_ammonia = next_pred.get('ammonia', 0)
                next_temp = next_pred.get('temperature', 0)
                next_humidity = next_pred.get('humidity', 0)
                next_light = next_pred.get('light', 0)
            else:
                next_ammonia = next_temp = next_humidity = next_light = 0
            
            print(f"\nPrediksi Sensor Berikutnya:")
            print(f"  Hasil Prediksi:")
            print(f"    Amonia: {next_ammonia:.2f} ppm")
            print(f"    Suhu: {next_temp:.2f} °C")
            print(f"    Kelembaban: {next_humidity:.2f} %")
            print(f"    Cahaya: {next_light:.2f} lux")
        
        # Expected values from Jupyter notebook
        print(f"\nExpected (dari Jupyter Notebook):")
        print(f"  Status: PERHATIAN (100% PERHATIAN)")
        print(f"  Anomali: ANOMALI (score: -0.6654)")
        print(f"  Prediksi: Amonia: 14.87, Suhu: 30.72, Kelembaban: 50.10, Cahaya: 337.83")
        
        # Compare
        print(f"\nPerbandingan:")
        # Status might be in 'label' field, check if it contains 'perhatian'
        status_match = 'perhatian' in status_label.lower() or 'PERHATIAN' in str(status_obj)
        print(f"  Status: {'MATCH' if status_match else 'MISMATCH'} (got: {status_label})")
        
        if 'prediction_6h' in data:
            pred_6h = data['prediction_6h']
            if isinstance(pred_6h, dict) and 'ammonia' in pred_6h:
                pred_ammonia = pred_6h['ammonia'][0] if len(pred_6h['ammonia']) > 0 else 0
                pred_temp = pred_6h['temperature'][0] if len(pred_6h['temperature']) > 0 else 0
                pred_humidity = pred_6h['humidity'][0] if len(pred_6h['humidity']) > 0 else 0
                pred_light = pred_6h['light'][0] if len(pred_6h['light']) > 0 else 0
            elif isinstance(pred_6h, list) and len(pred_6h) > 0:
                pred = pred_6h[0]
                pred_ammonia = pred.get('ammonia', 0)
                pred_temp = pred.get('temperature', 0)
                pred_humidity = pred.get('humidity', 0)
                pred_light = pred.get('light', 0)
            else:
                pred_ammonia = pred_temp = pred_humidity = pred_light = 0
            
            ammonia_match = abs(pred_ammonia - 14.87) < 2.0
            temp_match = abs(pred_temp - 30.72) < 2.0
            humidity_match = abs(pred_humidity - 50.10) < 2.0
            light_match = abs(pred_light - 337.83) < 2.0
            
            print(f"  Prediksi Amonia: {'MATCH' if ammonia_match else 'MISMATCH'} (got: {pred_ammonia:.2f}, expected: 14.87)")
            print(f"  Prediksi Suhu: {'MATCH' if temp_match else 'MISMATCH'} (got: {pred_temp:.2f}, expected: 30.72)")
            print(f"  Prediksi Kelembaban: {'MATCH' if humidity_match else 'MISMATCH'} (got: {pred_humidity:.2f}, expected: 50.10)")
            print(f"  Prediksi Cahaya: {'MATCH' if light_match else 'MISMATCH'} (got: {pred_light:.2f}, expected: 337.83)")
        
        else:
            print(f"\nError: HTTP {response.status_code}")
            print(f"Response: {response.text}")
        
except requests.exceptions.ConnectionError:
    print("\nError: Tidak dapat terhubung ke ML Service")
    print("Pastikan ML Service berjalan di http://localhost:5000")
except Exception as e:
    print(f"\nError: {str(e)}")

print("\n" + "=" * 70)

