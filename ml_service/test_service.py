"""
Script untuk test ML Service
"""

import requests
import json

BASE_URL = "http://localhost:5000"

def test_health():
    """Test health endpoint"""
    print("=" * 70)
    print("TEST 1: Health Check")
    print("=" * 70)
    try:
        response = requests.get(f"{BASE_URL}/health")
        print(f"Status Code: {response.status_code}")
        print(f"Response: {json.dumps(response.json(), indent=2)}")
        return response.status_code == 200
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_classify():
    """Test classify endpoint"""
    print("\n" + "=" * 70)
    print("TEST 2: Classify Status")
    print("=" * 70)
    try:
        data = {
            "ammonia": 22.5,
            "temperature": 29.5,
            "humidity": 62.0,
            "light": 250.0
        }
        response = requests.post(f"{BASE_URL}/classify", json=data)
        print(f"Status Code: {response.status_code}")
        result = response.json()
        print(f"Status: {result.get('status')}")
        print(f"Confidence: {result.get('confidence', 0) * 100:.1f}%")
        print(f"Probabilities:")
        for label, prob in result.get('probability', {}).items():
            print(f"  {label}: {prob * 100:.1f}%")
        return response.status_code == 200
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_anomaly():
    """Test anomaly detection"""
    print("\n" + "=" * 70)
    print("TEST 3: Anomaly Detection")
    print("=" * 70)
    try:
        data = {
            "ammonia": 35.0,  # High ammonia
            "temperature": 32.0,  # High temperature
            "humidity": 62.0,
            "light": 250.0
        }
        response = requests.post(f"{BASE_URL}/anomaly", json=data)
        print(f"Status Code: {response.status_code}")
        result = response.json()
        print(f"Is Anomaly: {result.get('is_anomaly')}")
        print(f"Status: {result.get('status')}")
        print(f"Anomaly Score: {result.get('anomaly_score', 0):.4f}")
        return response.status_code == 200
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_predict():
    """Test main predict endpoint"""
    print("\n" + "=" * 70)
    print("TEST 4: Main Prediction (Full Pipeline)")
    print("=" * 70)
    try:
        # Load test data
        with open('test_data.json', 'r') as f:
            test_data = json.load(f)
        
        response = requests.post(f"{BASE_URL}/predict", json=test_data)
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"\n✅ Prediction Success!")
            print(f"Model: {result.get('model_name')}")
            print(f"Version: {result.get('model_version')}")
            print(f"Accuracy: {result.get('accuracy')}")
            print(f"Confidence: {result.get('confidence')}")
            print(f"Prediction Time: {result.get('prediction_time')}ms")
            
            print(f"\n📊 Status Kandang:")
            status = result.get('status', {})
            print(f"  Label: {status.get('label')}")
            print(f"  Severity: {status.get('severity')}")
            print(f"  Message: {status.get('message')}")
            
            print(f"\n🔮 Prediction 6h (first 3):")
            pred_6h = result.get('prediction_6h', {})
            for i in range(min(3, len(pred_6h.get('temperature', [])))):
                print(f"  Hour {i+1}: Temp={pred_6h['temperature'][i]:.1f}°C, "
                      f"Humidity={pred_6h['humidity'][i]:.1f}%, "
                      f"Ammonia={pred_6h['ammonia'][i]:.1f}ppm, "
                      f"Light={pred_6h['light'][i]:.0f}lux")
            
            anomalies = result.get('anomalies', [])
            print(f"\n🚨 Anomalies Detected: {len(anomalies)}")
            for i, anomaly in enumerate(anomalies[:3]):  # Show first 3
                print(f"  {i+1}. {anomaly.get('type')}: {anomaly.get('message')} "
                      f"(Severity: {anomaly.get('severity')})")
            
            return True
        else:
            print(f"❌ Error: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == "__main__":
    print("\n🧪 Testing ML Service")
    print("=" * 70)
    print("Pastikan ML service sudah berjalan: python app.py")
    print("=" * 70)
    
    results = []
    results.append(("Health Check", test_health()))
    results.append(("Classify", test_classify()))
    results.append(("Anomaly Detection", test_anomaly()))
    results.append(("Full Prediction", test_predict()))
    
    print("\n" + "=" * 70)
    print("TEST SUMMARY")
    print("=" * 70)
    for test_name, passed in results:
        status = "✅ PASS" if passed else "❌ FAIL"
        print(f"{test_name}: {status}")
    
    all_passed = all(result[1] for result in results)
    print("\n" + "=" * 70)
    if all_passed:
        print("🎉 All tests passed! Service is ready.")
    else:
        print("⚠️  Some tests failed. Please check the errors above.")
    print("=" * 70)

