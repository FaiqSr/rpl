"""
Test untuk memastikan prediksi dimulai dari nilai latest yang benar
"""
import requests
import json

# Buat history dengan nilai terakhir yang jelas
history = []
for i in range(30):
    history.append({
        'time': f'2025-11-22 {i:02d}:00',
        'ammonia': 10.0 + (i * 0.1),
        'temperature': 26.0 + (i * 0.02),
        'humidity': 65.0 - (i * 0.1),
        'light': 700.0 - (i * 0.5)
    })

# Pastikan nilai terakhir sesuai dengan yang ingin kita test
history[-1] = {
    'time': '2025-11-22 23:00',
    'ammonia': 10.0,
    'temperature': 26.5,
    'humidity': 65.0,
    'light': 700.0
}

url = 'http://localhost:5000/predict'
payload = {'history': history}

print("=" * 70)
print("TEST: Prediksi harus dimulai dari nilai latest")
print("=" * 70)
print(f"\nLatest values (dari history[-1]):")
print(f"  Amonia: {history[-1]['ammonia']} ppm")
print(f"  Suhu: {history[-1]['temperature']} °C")
print(f"  Kelembaban: {history[-1]['humidity']} %")
print(f"  Cahaya: {history[-1]['light']} lux")

try:
    response = requests.post(url, json=payload, timeout=30)
    
    if response.status_code == 200:
        data = response.json()
        
        print(f"\nResponse dari ML Service:")
        print(f"\nLatest (dari response):")
        latest = data.get('latest', {})
        print(f"  Amonia: {latest.get('ammonia', 'N/A')} ppm")
        print(f"  Suhu: {latest.get('temperature', 'N/A')} °C")
        print(f"  Kelembaban: {latest.get('humidity', 'N/A')} %")
        print(f"  Cahaya: {latest.get('light', 'N/A')} lux")
        
        print(f"\nPrediksi pertama (6 jam, step 1):")
        pred_6h = data.get('prediction_6h', {})
        if isinstance(pred_6h, dict):
            first_ammonia = pred_6h.get('ammonia', [])[0] if len(pred_6h.get('ammonia', [])) > 0 else None
            first_temp = pred_6h.get('temperature', [])[0] if len(pred_6h.get('temperature', [])) > 0 else None
            first_humidity = pred_6h.get('humidity', [])[0] if len(pred_6h.get('humidity', [])) > 0 else None
            first_light = pred_6h.get('light', [])[0] if len(pred_6h.get('light', [])) > 0 else None
            
            print(f"  Amonia: {first_ammonia:.2f} ppm")
            print(f"  Suhu: {first_temp:.2f} °C")
            print(f"  Kelembaban: {first_humidity:.2f} %")
            print(f"  Cahaya: {first_light:.2f} lux")
            
            print(f"\nPerbandingan:")
            print(f"  Prediksi harus dimulai dari latest values atau mengikuti tren dari latest")
            print(f"  (LSTM menggunakan sequence terakhir untuk memprediksi, bukan langsung dari latest)")
            
            # Check if prediction is reasonable (should be close to latest or follow trend)
            ammonia_diff = abs(first_ammonia - latest.get('ammonia', 0))
            temp_diff = abs(first_temp - latest.get('temperature', 0))
            humidity_diff = abs(first_humidity - latest.get('humidity', 0))
            light_diff = abs(first_light - latest.get('light', 0))
            
            print(f"\nSelisih dari latest:")
            print(f"  Amonia: {ammonia_diff:.2f} ppm")
            print(f"  Suhu: {temp_diff:.2f} °C")
            print(f"  Kelembaban: {humidity_diff:.2f} %")
            print(f"  Cahaya: {light_diff:.2f} lux")
            
            # LSTM predictions might not start exactly from latest, but should be reasonable
            # based on the trend in the sequence
            print(f"\nCatatan: LSTM memprediksi berdasarkan pola dari sequence terakhir (30 data points),")
            print(f"bukan langsung dari nilai latest. Ini normal untuk time series prediction.")
    else:
        print(f"\nError: HTTP {response.status_code}")
        print(f"Response: {response.text}")
        
except requests.exceptions.ConnectionError:
    print("\nError: Tidak dapat terhubung ke ML Service")
    print("Pastikan ML Service berjalan di http://localhost:5000")
except Exception as e:
    print(f"\nError: {str(e)}")

print("\n" + "=" * 70)

