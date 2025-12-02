"""
Flask API Service untuk Machine Learning Monitoring Kandang Ayam
Menggunakan model LSTM, Random Forest, dan Isolation Forest
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import joblib
from tensorflow.keras.models import load_model
import json
import os
from datetime import datetime
import logging
import time
from functools import wraps
from logging.handlers import RotatingFileHandler

app = Flask(__name__)
CORS(app)  # Enable CORS untuk Laravel

# Setup Logging
log_dir = os.path.join(os.path.dirname(__file__), 'logs')
os.makedirs(log_dir, exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        RotatingFileHandler(
            os.path.join(log_dir, 'ml_service.log'),
            maxBytes=10*1024*1024,  # 10MB
            backupCount=5
        ),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Metrics tracking
prediction_metrics = {
    'total_requests': 0,
    'successful_predictions': 0,
    'failed_predictions': 0,
    'avg_processing_time': 0.0,
    'model_performance': {
        'lstm': {'count': 0, 'avg_time': 0.0},
        'random_forest': {'count': 0, 'avg_time': 0.0},
        'isolation_forest': {'count': 0, 'avg_time': 0.0}
    }
}

# Konstanta
SEQUENCE_LENGTH = 30  # Sesuaikan dengan model LSTM Anda

# Load Models & Scalers
MODEL_DIR = os.path.join(os.path.dirname(__file__), 'models')
ENSEMBLE_DIR = os.path.join(MODEL_DIR, 'models_ensemble')

try:
    # Load Random Forest SMOTE sebagai primary, dengan fallback ke model biasa
    rf_smote_path = os.path.join(MODEL_DIR, 'model_random_forest_smote.pkl')
    rf_fallback_path = os.path.join(MODEL_DIR, 'model_random_forest.pkl')
    
    if os.path.exists(rf_smote_path):
        model_rf = joblib.load(rf_smote_path)
        print("✅ Random Forest SMOTE loaded (primary)")
    else:
        model_rf = joblib.load(rf_fallback_path)
        print("⚠️  Random Forest SMOTE not found, using fallback model")
    
    scaler_rf = joblib.load(os.path.join(MODEL_DIR, 'scaler_rf.pkl'))
    
    # Load Ensemble LSTM sebagai primary, dengan fallback ke model single
    ensemble_config_path = os.path.join(ENSEMBLE_DIR, 'ensemble_config.json')
    lstm_fallback_path = os.path.join(MODEL_DIR, 'model_lstm_kandang.h5')
    
    model_lstm = None
    model_lstm_ensemble = None
    use_ensemble = False
    
    if os.path.exists(ensemble_config_path):
        try:
            with open(ensemble_config_path, 'r') as f:
                ensemble_config = json.load(f)
            
            n_models = ensemble_config.get('n_models', 3)
            model_files = ensemble_config.get('model_files', [])
            ensemble_method = ensemble_config.get('method', 'average')
            
            # Load semua model ensemble
            ensemble_models = []
            for model_file in model_files:
                model_path = os.path.join(ENSEMBLE_DIR, model_file)
                if os.path.exists(model_path):
                    ensemble_models.append(load_model(model_path))
                else:
                    print(f"⚠️  Ensemble model {model_file} not found")
            
            if len(ensemble_models) == n_models:
                model_lstm_ensemble = ensemble_models
                use_ensemble = True
                print(f"✅ Ensemble LSTM loaded ({n_models} models, method: {ensemble_method})")
            else:
                print(f"⚠️  Not all ensemble models found ({len(ensemble_models)}/{n_models}), using fallback")
                model_lstm = load_model(lstm_fallback_path)
        except Exception as e:
            print(f"⚠️  Error loading ensemble: {e}, using fallback")
            model_lstm = load_model(lstm_fallback_path)
    else:
        model_lstm = load_model(lstm_fallback_path)
        print("✅ Single LSTM loaded (fallback)")
    
    scaler_lstm = joblib.load(os.path.join(MODEL_DIR, 'scaler_lstm.pkl'))
    
    # Load Isolation Forest untuk deteksi anomali
    model_if = joblib.load(os.path.join(MODEL_DIR, 'model_isolation_forest.pkl'))
    scaler_if = joblib.load(os.path.join(MODEL_DIR, 'scaler_if.pkl'))
    
    # Load metadata
    with open(os.path.join(MODEL_DIR, 'model_metadata.json'), 'r') as f:
        model_metadata = json.load(f)
    
    # Load threshold_config.json untuk threshold optimal
    threshold_config_path = os.path.join(MODEL_DIR, 'threshold_config.json')
    threshold_config = None
    if os.path.exists(threshold_config_path):
        try:
            with open(threshold_config_path, 'r') as f:
                threshold_config = json.load(f)
            print(f"✅ Threshold config loaded: best_threshold={threshold_config.get('best_threshold', 'N/A')}")
        except Exception as e:
            print(f"⚠️  Error loading threshold config: {e}")
    else:
        print("⚠️  Threshold config not found, using default")
    
    # Extract model info from metadata
    lstm_info = model_metadata.get('models', {}).get('lstm', {})
    rf_info = model_metadata.get('models', {}).get('random_forest', {})
    
    # Load sensor statistics (jika ada) untuk deteksi anomali yang lebih akurat
    SENSOR_STATS_FILE = os.path.join(MODEL_DIR, 'sensor_stats.json')
    SENSOR_STATS = None
    if os.path.exists(SENSOR_STATS_FILE):
        try:
            with open(SENSOR_STATS_FILE, 'r') as f:
                SENSOR_STATS = json.load(f)
            print("✅ Statistik sensor berhasil dimuat!")
        except Exception as e:
            print(f"⚠️  Gagal memuat statistik sensor: {e}")
            SENSOR_STATS = None
    else:
        # Generate default stats dari metadata thresholds
        SENSOR_STATS = {
            'ammonia': {'mean': 15, 'std': 5, 'min': 0, 'max': 35},
            'temperature': {'mean': 28, 'std': 3, 'min': 20, 'max': 35},
            'humidity': {'mean': 60, 'std': 8, 'min': 50, 'max': 80},
            'light': {'mean': 35, 'std': 15, 'min': 1, 'max': 85}  # Data puluhan (threshold 10-60 lux)
        }
        print("⚠️  Statistik sensor tidak ditemukan, menggunakan default dari metadata")
    
    MODELS_LOADED = True
    print("✅ Semua model berhasil dimuat!")
    if use_ensemble:
        print(f"📊 LSTM: Ensemble ({len(model_lstm_ensemble)} models)")
    else:
        print(f"📊 LSTM: Single model ({lstm_info.get('architecture', 'N/A')})")
    print(f"🌲 Random Forest: {'SMOTE' if os.path.exists(rf_smote_path) else 'Standard'} - Accuracy {rf_info.get('accuracy', 'N/A')}")
    print(f"🔍 Isolation Forest: Contamination {model_metadata.get('models', {}).get('isolation_forest', {}).get('contamination', 'N/A')}")
except Exception as e:
    MODELS_LOADED = False
    print(f"❌ Error loading models: {e}")
    import traceback
    traceback.print_exc()
    model_metadata = {
        'model_name': 'Error Loading Models',
        'model_version': '1.0',
        'accuracy': None
    }
    threshold_config = None
    use_ensemble = False
    model_lstm_ensemble = None
    model_lstm = None
    model_rf = None
    scaler_rf = None
    scaler_lstm = None
    model_if = None
    scaler_if = None
    SENSOR_STATS = None


def log_performance(func):
    """Decorator untuk log waktu eksekusi"""
    @wraps(func)
    def wrapper(*args, **kwargs):
        start_time = time.time()
        try:
            result = func(*args, **kwargs)
            execution_time = time.time() - start_time
            
            # Update metrics
            model_name = func.__name__.replace('predict_', '').replace('_', '_')
            if model_name in prediction_metrics['model_performance']:
                metrics = prediction_metrics['model_performance'][model_name]
                metrics['count'] += 1
                metrics['avg_time'] = (
                    (metrics['avg_time'] * (metrics['count'] - 1) + execution_time)
                    / metrics['count']
                )
            
            logger.info(f'{func.__name__} executed in {execution_time:.3f}s')
            return result
        except Exception as e:
            execution_time = time.time() - start_time
            logger.error(f'{func.__name__} failed after {execution_time:.3f}s: {str(e)}', exc_info=True)
            raise
    return wrapper


def predict_status_robust(sensor_data, model_rf, scaler_rf, threshold_config):
    """
    Prediksi status dengan logika threshold yang lebih robust
    Input: sensor_data = [amonia, suhu, kelembaban, cahaya]
    """
    # Scale input
    X = np.array([sensor_data])
    X_scaled = scaler_rf.transform(X)
    
    # Get probabilities
    proba = model_rf.predict_proba(X_scaled)[0]
    
    # Mapping classes: model bisa dilatih dengan classes [1, 2] atau [0, 1, 2]
    # Cek classes yang dilatih
    classes_trained = model_rf.classes_
    
    # Map probabilitas ke label
    proba_dict = {}
    for idx, class_label in enumerate(classes_trained):
        proba_dict[class_label] = float(proba[idx])
    
    # Jika model hanya dilatih untuk [1, 2] (PERHATIAN, BURUK)
    # BAIK = 1 - (PERHATIAN + BURUK)
    if len(classes_trained) == 2 and 0 not in classes_trained:
        # Classes: [1, 2] = PERHATIAN, BURUK
        proba_perhatian = proba_dict.get(1, 0.0)
        proba_buruk = proba_dict.get(2, 0.0)
        proba_baik = max(0.0, 1.0 - (proba_perhatian + proba_buruk))
    else:
        # Classes: [0, 1, 2] = BAIK, PERHATIAN, BURUK
        proba_baik = proba_dict.get(0, 0.0)
        proba_perhatian = proba_dict.get(1, 0.0)
        proba_buruk = proba_dict.get(2, 0.0)
    
    # Load threshold optimal
    best_threshold = threshold_config.get('best_threshold', 0.55) if threshold_config else 0.55
    
    # LOGIKA PRIORITAS:
    # 1. Jika probabilitas BURUK >= threshold → BURUK (prioritas tertinggi)
    # 2. Jika probabilitas PERHATIAN > BURUK dan PERHATIAN > BAIK → PERHATIAN
    # 3. Jika probabilitas BAIK tertinggi → BAIK
    
    if proba_buruk >= best_threshold:
        status = 'BURUK'
        status_code = 2
    elif proba_perhatian > proba_buruk and proba_perhatian > proba_baik:
        status = 'PERHATIAN'
        status_code = 1
    else:
        status = 'BAIK'
        status_code = 0
    
    return {
        'status': status,
        'status_code': status_code,
        'probabilities': {
            'BAIK': float(proba_baik),
            'PERHATIAN': float(proba_perhatian),
            'BURUK': float(proba_buruk)
        },
        'confidence': float(max(proba_baik, proba_perhatian, proba_buruk)),
        'threshold_used': float(best_threshold)
    }


@log_performance
def predict_sensor_classification(amonia, suhu, kelembaban, cahaya):
    """
    Klasifikasi status kandang dari data sensor (model v2)
    Input & model sekarang dalam skala ASLI (lux, ppm, °C, %)
    Menggunakan fungsi predict_status_robust untuk logika yang lebih jelas
    """
    sensor_data = [amonia, suhu, kelembaban, cahaya]
    result = predict_status_robust(sensor_data, model_rf, scaler_rf, threshold_config)
    
    status_label = result['status'].lower()
    probability_dict = result['probabilities']
    
    # Mapping untuk backward compatibility
    status_labels_map = {1: 'PERHATIAN', 2: 'BURUK'}
    colors = {0: '#2ecc71', 1: '#f39c12', 2: '#e74c3c'}
    
    # Generate message berdasarkan status
    status_messages = {
        'baik': 'Semua parameter lingkungan dalam kondisi optimal. Kandang siap untuk pertumbuhan ayam yang sehat.',
        'perhatian': 'Beberapa parameter lingkungan perlu diperhatikan. Lakukan pengecekan ventilasi, suhu, dan kelembaban. Periksa juga ketersediaan pakan dan air minum.',
        'buruk': 'Kondisi lingkungan tidak optimal dan berpotensi membahayakan kesehatan ayam. Segera lakukan penyesuaian suhu, kelembaban, ventilasi, atau pencahayaan. Jika perlu, hubungi dokter hewan.'
    }
    
    severity_map = {
        'baik': 'normal',
        'perhatian': 'warning',
        'buruk': 'critical'
    }
    
    confidence = result['confidence']
    if confidence >= 0.8:
        confidence_desc = 'Sangat yakin'
    elif confidence >= 0.6:
        confidence_desc = 'Cukup yakin'
    else:
        confidence_desc = 'Perlu verifikasi manual'
    
    message = f"{status_messages.get(status_label, 'Status tidak dapat ditentukan')} (Tingkat keyakinan sistem: {confidence_desc})"
    
    return {
        'label': status_label,
        'status': result['status'],  # BAIK, PERHATIAN, BURUK (uppercase)
        'severity': severity_map.get(status_label, 'normal'),
        'message': message,
        'confidence': confidence,
        'probability': probability_dict,
        'threshold_used': result['threshold_used']
    }


def generate_status_message(status_label, confidence):
    """
    Generate status message yang informatif dan mudah dipahami peternak
    """
    confidence_pct = confidence * 100
    
    status_messages = {
        'BAIK': 'Semua parameter lingkungan dalam kondisi optimal. Kandang siap untuk pertumbuhan ayam yang sehat.',
        'PERHATIAN': 'Beberapa parameter lingkungan perlu diperhatikan. Lakukan pengecekan ventilasi, suhu, dan kelembaban. Periksa juga ketersediaan pakan dan air minum.',
        'BURUK': 'Kondisi lingkungan tidak optimal dan berpotensi membahayakan kesehatan ayam. Segera lakukan penyesuaian suhu, kelembaban, ventilasi, atau pencahayaan. Jika perlu, hubungi dokter hewan.'
    }
    
    # Confidence level description
    if confidence_pct >= 80:
        confidence_desc = 'Sangat yakin'
    elif confidence_pct >= 60:
        confidence_desc = 'Cukup yakin'
    else:
        confidence_desc = 'Perlu verifikasi manual'
    
    # Message yang informatif dengan action items
    base_message = status_messages.get(status_label, 'Status tidak dapat ditentukan. Silakan refresh halaman.')
    message = f"{base_message} (Tingkat keyakinan sistem: {confidence_desc})"
    
    return message


def detect_anomaly(amonia, suhu, kelembaban, cahaya):
    """
    Deteksi anomali menggunakan Isolation Forest + threshold-based identification
    - Isolation Forest sebagai penentu utama (prediction == -1)
    - Early return jika tidak anomali
    - Identifikasi sensor menggunakan threshold (lebih praktis)
    - Fallback dengan deviasi/z-score jika threshold tidak mendeteksi sensor spesifik
    """
    X = np.array([[amonia, suhu, kelembaban, cahaya]])
    X_scaled = scaler_if.transform(X)
    prediction = model_if.predict(X_scaled)[0]
    score = model_if.score_samples(X_scaled)[0]
    
    # HANYA gunakan hasil Isolation Forest untuk menentukan apakah ini anomali
    is_anomaly = prediction == -1
    
    # Early return jika tidak anomali
    if not is_anomaly:
        return {
            'is_anomaly': False,
            'anomaly_score': float(score),
            'status': 'NORMAL',
            'anomaly_sensors': [],
            'anomaly_details': [],
            'sensor_values': {
                'ammonia': float(amonia),
                'temperature': float(suhu),
                'humidity': float(kelembaban),
                'light': float(cahaya)
            }
        }
    
    # Jika Isolation Forest MENDETEKSI anomali, identifikasi sensor spesifik
    anomaly_sensors = []
    anomaly_details = []
    
    # Threshold dari model_metadata.json (sesuai aturan boiler)
    rf_thresholds = model_metadata.get('models', {}).get('random_forest', {}).get('thresholds', {})
    thresholds = {
        'amonia_ppm': rf_thresholds.get('amonia_ppm', {'ideal_max': 20, 'warn_max': 35, 'danger_max': 35}),
        'suhu_c': rf_thresholds.get('suhu_c', {'ideal_min': 23, 'ideal_max': 34, 'danger_low': 23, 'danger_high': 34}),
        'kelembaban_rh': rf_thresholds.get('kelembaban_rh', {'ideal_min': 50, 'ideal_max': 70, 'warn_high': 80, 'danger_high': 80}),
        # Threshold cahaya: sesuai aturan boiler (10-60 lux), bukan disesuaikan dataset
        'cahaya_lux': rf_thresholds.get('cahaya_lux', {'ideal_low': 20, 'ideal_high': 40, 'warn_low': 10, 'warn_high': 60})
    }
    
    # Label dan unit untuk pesan
    sensor_labels = {
        'ammonia': 'Amoniak',
        'temperature': 'Suhu',
        'humidity': 'Kelembaban',
        'light': 'Cahaya'
    }
    units = {
        'ammonia': 'ppm',
        'temperature': '°C',
        'humidity': '%',
        'light': 'lux'
    }
    
    # Cek setiap sensor berdasarkan threshold
    # Amonia
    if amonia > thresholds['amonia_ppm']['danger_max']:
        anomaly_sensors.append('ammonia')
        anomaly_details.append({
            'sensor': 'ammonia',
            'value': amonia,
            'message': f'Amoniak berbahaya (nilai: {amonia:.1f} ppm, di atas {thresholds["amonia_ppm"]["danger_max"]} ppm)',
            'severity': 'critical'
        })
    elif amonia > thresholds['amonia_ppm']['warn_max']:
        anomaly_sensors.append('ammonia')
        anomaly_details.append({
            'sensor': 'ammonia',
            'value': amonia,
            'message': f'Amoniak tinggi (nilai: {amonia:.1f} ppm, di atas {thresholds["amonia_ppm"]["warn_max"]} ppm)',
            'severity': 'warning'
        })
    
    # Suhu
    if suhu < thresholds['suhu_c']['danger_low']:
        anomaly_sensors.append('temperature')
        anomaly_details.append({
            'sensor': 'temperature',
            'value': suhu,
            'message': f'Suhu terlalu rendah (nilai: {suhu:.1f}°C, di bawah {thresholds["suhu_c"]["danger_low"]}°C)',
            'severity': 'critical'
        })
    elif suhu > thresholds['suhu_c']['danger_high']:
        anomaly_sensors.append('temperature')
        anomaly_details.append({
            'sensor': 'temperature',
            'value': suhu,
            'message': f'Suhu terlalu tinggi (nilai: {suhu:.1f}°C, di atas {thresholds["suhu_c"]["danger_high"]}°C)',
            'severity': 'critical'
        })
    
    # Kelembaban: Warning untuk <50% atau >70%, Danger untuk >80%
    if kelembaban < thresholds['kelembaban_rh']['ideal_min']:
        anomaly_sensors.append('humidity')
        anomaly_details.append({
            'sensor': 'humidity',
            'value': kelembaban,
            'message': f'Kelembaban terlalu rendah (nilai: {kelembaban:.1f}%, di bawah {thresholds["kelembaban_rh"]["ideal_min"]}%)',
            'severity': 'warning'
        })
    elif kelembaban > thresholds['kelembaban_rh']['ideal_max'] and kelembaban <= thresholds['kelembaban_rh']['warn_high']:
        anomaly_sensors.append('humidity')
        anomaly_details.append({
            'sensor': 'humidity',
            'value': kelembaban,
            'message': f'Kelembaban terlalu tinggi (nilai: {kelembaban:.1f}%, di atas {thresholds["kelembaban_rh"]["ideal_max"]}%)',
            'severity': 'warning'
        })
    elif kelembaban > thresholds['kelembaban_rh']['danger_high']:
        anomaly_sensors.append('humidity')
        anomaly_details.append({
            'sensor': 'humidity',
            'value': kelembaban,
            'message': f'Kelembaban berbahaya (nilai: {kelembaban:.1f}%, di atas {thresholds["kelembaban_rh"]["danger_high"]}%)',
            'severity': 'critical'
        })
    
    # Cahaya: threshold 10-60 lux (aturan boiler)
    # Catatan: nilai aktual mungkin ratusan, tapi threshold tetap 10-60
    if cahaya < thresholds['cahaya_lux']['warn_low']:
        anomaly_sensors.append('light')
        anomaly_details.append({
            'sensor': 'light',
            'value': cahaya,
            'message': f'Cahaya kurang optimal (nilai: {cahaya:.1f} lux, di bawah {thresholds["cahaya_lux"]["warn_low"]} lux)',
            'severity': 'warning'
        })
    elif cahaya > thresholds['cahaya_lux']['warn_high']:
        anomaly_sensors.append('light')
        anomaly_details.append({
            'sensor': 'light',
            'value': cahaya,
            'message': f'Cahaya terlalu tinggi (nilai: {cahaya:.1f} lux, di atas {thresholds["cahaya_lux"]["warn_high"]} lux)',
            'severity': 'critical'
        })
    
    # SELALU jalankan fallback untuk menambahkan sensor lain yang menyimpang
    # Bahkan jika threshold sudah mendeteksi beberapa sensor, tetap cek sensor lain menggunakan z-score/deviasi
    # Ini memastikan semua sensor yang menyimpang terdeteksi, bukan hanya yang melanggar threshold
    sensor_values_map = {
        'ammonia': amonia,
        'temperature': suhu,
        'humidity': kelembaban,
        'light': cahaya
    }
    
    # Prioritas 1: Gunakan z-score dari statistik dataset jika tersedia
    use_stats = SENSOR_STATS is not None
    if use_stats:
        deviations = {}
        for sensor_name, value in sensor_values_map.items():
            # Skip sensor yang sudah terdeteksi oleh threshold
            if sensor_name in anomaly_sensors:
                continue
                
            stats = SENSOR_STATS.get(sensor_name, {})
            mean = stats.get('mean', 0)
            std = stats.get('std', 1)
            if std > 0:
                # Z-score: berapa standar deviasi dari mean
                z_score = abs(value - mean) / std
                deviations[sensor_name] = z_score
            else:
                deviations[sensor_name] = 0
        
        # Ambil sensor dengan z-score tinggi (menyimpang dari mean)
        sorted_deviations = sorted(deviations.items(), key=lambda x: x[1], reverse=True)
        
        # Tambahkan sensor dengan z-score > 1.0 (menyimpang signifikan)
        for sensor_name, z_score in sorted_deviations:
            if z_score > 1.0 and sensor_name not in anomaly_sensors:
                anomaly_sensors.append(sensor_name)
                value = sensor_values_map[sensor_name]
                severity = 'critical' if z_score > 2.0 else 'warning'
                
                anomaly_details.append({
                    'sensor': sensor_name,
                    'value': value,
                    'message': f'{sensor_labels.get(sensor_name, sensor_name)} menyimpang (nilai: {value:.1f} {units.get(sensor_name, "")}, z-score: {z_score:.2f})',
                    'severity': severity,
                    'z_score': float(z_score)
                })
        
        # Jika tidak ada sensor dengan z-score > 1.0, ambil top 2 sensor dengan deviasi terbesar
        if len(anomaly_sensors) == 0:
            for sensor_name, z_score in sorted_deviations[:2]:
                if sensor_name not in anomaly_sensors:
                    anomaly_sensors.append(sensor_name)
                    value = sensor_values_map[sensor_name]
                    severity = 'critical' if z_score > 1.5 else 'warning'
                    
                    anomaly_details.append({
                        'sensor': sensor_name,
                        'value': value,
                        'message': f'{sensor_labels.get(sensor_name, sensor_name)} menyimpang (nilai: {value:.1f} {units.get(sensor_name, "")}, z-score: {z_score:.2f})',
                        'severity': severity,
                        'z_score': float(z_score)
                    })
    else:
        # Prioritas 2: Fallback dengan deviasi relatif dari nilai normal
        # Nilai normal berdasarkan dataset training (dari histogram)
        deviations = {}
        for sensor_name, value in sensor_values_map.items():
            # Skip sensor yang sudah terdeteksi oleh threshold
            if sensor_name in anomaly_sensors:
                continue
                
            if sensor_name == 'ammonia':
                deviations[sensor_name] = abs(value - 15) / 15 if value > 0 else 0  # Normal ~15 ppm
            elif sensor_name == 'temperature':
                deviations[sensor_name] = abs(value - 29) / 29 if value > 0 else 0  # Normal ~29°C
            elif sensor_name == 'humidity':
                deviations[sensor_name] = abs(value - 51) / 51 if value > 0 else 0  # Normal ~51%
            elif sensor_name == 'light':
                deviations[sensor_name] = abs(value - 35) / 35 if value > 0 else 0  # Normal ~35 lux (rata-rata 10-60)
        
        # Ambil sensor dengan deviasi terbesar (top 2)
        if deviations:
            sorted_deviations = sorted(deviations.items(), key=lambda x: x[1], reverse=True)
            for sensor_name, dev in sorted_deviations[:2]:
                if dev > 0.1 and sensor_name not in anomaly_sensors:  # Hanya jika deviasi signifikan (>10%)
                    anomaly_sensors.append(sensor_name)
                    anomaly_details.append({
                        'sensor': sensor_name,
                        'value': sensor_values_map[sensor_name],
                        'message': f'{sensor_labels.get(sensor_name, sensor_name)} menyimpang (nilai: {sensor_values_map[sensor_name]:.1f} {units.get(sensor_name, "")})',
                        'severity': 'warning'
                    })
    
    # Jika setelah semua pengecekan masih tidak ada sensor yang terdeteksi (sangat jarang)
    # Gunakan sensor dengan deviasi terbesar sebagai fallback terakhir
    if len(anomaly_sensors) == 0:
        # Fallback terakhir: ambil sensor dengan nilai paling ekstrem
        max_dev_sensor = 'ammonia'  # Default
        max_dev = 0
        for sensor_name, value in sensor_values_map.items():
            if sensor_name == 'ammonia':
                dev = abs(value - 15) / 15 if value > 0 else 0
            elif sensor_name == 'temperature':
                dev = abs(value - 29) / 29 if value > 0 else 0
            elif sensor_name == 'humidity':
                dev = abs(value - 51) / 51 if value > 0 else 0
            elif sensor_name == 'light':
                dev = abs(value - 35) / 35 if value > 0 else 0  # Normal ~35 lux
            else:
                dev = 0
            
            if dev > max_dev:
                max_dev = dev
                max_dev_sensor = sensor_name
        
        if max_dev > 0:
            anomaly_sensors.append(max_dev_sensor)
            anomaly_details.append({
                'sensor': max_dev_sensor,
                'value': sensor_values_map[max_dev_sensor],
                'message': f'{sensor_labels.get(max_dev_sensor, max_dev_sensor)} menyimpang (nilai: {sensor_values_map[max_dev_sensor]:.1f} {units.get(max_dev_sensor, "")})',
                'severity': 'warning'
            })
        sensor_values_map = {
            'ammonia': amonia,
            'temperature': suhu,
            'humidity': kelembaban,
            'light': cahaya
        }
        
        # Prioritas 1: Gunakan z-score dari statistik dataset jika tersedia
        use_stats = SENSOR_STATS is not None
        if use_stats:
            deviations = {}
            for sensor_name, value in sensor_values_map.items():
                stats = SENSOR_STATS.get(sensor_name, {})
                mean = stats.get('mean', 0)
                std = stats.get('std', 1)
                if std > 0:
                    # Z-score: berapa standar deviasi dari mean
                    z_score = abs(value - mean) / std
                    deviations[sensor_name] = z_score
                else:
                    deviations[sensor_name] = 0
            
            # Ambil sensor dengan z-score tinggi (menyimpang dari mean)
            sorted_deviations = sorted(deviations.items(), key=lambda x: x[1], reverse=True)
            
            # Ambil semua sensor dengan z-score > 1.0 atau top 2 jika tidak ada yang > 1.0
            for sensor_name, z_score in sorted_deviations:
                if z_score > 1.0 or len(anomaly_sensors) < 2:
                    if sensor_name not in anomaly_sensors:
                        anomaly_sensors.append(sensor_name)
                        value = sensor_values_map[sensor_name]
                        severity = 'critical' if z_score > 2.0 else 'warning'
                        
                        anomaly_details.append({
                            'sensor': sensor_name,
                            'value': value,
                            'message': f'{sensor_labels.get(sensor_name, sensor_name)} menyimpang (nilai: {value:.1f} {units.get(sensor_name, "")}, z-score: {z_score:.2f})',
                            'severity': severity,
                            'z_score': float(z_score)
                        })
        else:
            # Prioritas 2: Fallback dengan deviasi relatif dari nilai normal
            # Nilai normal berdasarkan dataset training (dari histogram)
            deviations = {
                'ammonia': abs(amonia - 15) / 15 if amonia > 0 else 0,  # Normal ~15 ppm
                'temperature': abs(suhu - 29) / 29 if suhu > 0 else 0,  # Normal ~29°C
                'humidity': abs(kelembaban - 51) / 51 if kelembaban > 0 else 0,  # Normal ~51%
                'light': abs(cahaya - 35) / 35 if cahaya > 0 else 0  # Normal ~35 lux (rata-rata 10-60)
            }
            
            # Sensor dengan deviasi terbesar
            max_dev_sensor = max(deviations, key=deviations.get)
            if deviations[max_dev_sensor] > 0.1:  # Hanya jika deviasi signifikan (>10%)
                anomaly_sensors.append(max_dev_sensor)
                anomaly_details.append({
                    'sensor': max_dev_sensor,
                    'value': sensor_values_map[max_dev_sensor],
                    'message': f'{sensor_labels.get(max_dev_sensor, max_dev_sensor)} menyimpang (nilai: {sensor_values_map[max_dev_sensor]:.1f} {units.get(max_dev_sensor, "")})',
                    'severity': 'warning'
                })
    
    return {
        'is_anomaly': bool(is_anomaly),
        'anomaly_score': float(score),
        'status': 'ANOMALI' if is_anomaly else 'NORMAL',
        'anomaly_sensors': anomaly_sensors,  # List sensor yang anomali
        'anomaly_details': anomaly_details,  # Detail untuk setiap sensor
        'sensor_values': {
            'ammonia': float(amonia),
            'temperature': float(suhu),
            'humidity': float(kelembaban),
            'light': float(cahaya)
        }
    }


def predict_next_sensor_values(recent_history):
    """
    Prediksi nilai sensor berikutnya (skala asli)
    recent_history: list/array [[amonia, suhu, kelembaban, cahaya], ...] panjang >= SEQUENCE_LENGTH
    Menggunakan ensemble LSTM jika tersedia, fallback ke single model
    """
    if len(recent_history) < SEQUENCE_LENGTH:
        return {'error': f'Need at least {SEQUENCE_LENGTH} historical data points'}
    
    seq = np.array(recent_history[-SEQUENCE_LENGTH:]).copy()
    last_sequence_scaled = scaler_lstm.transform(seq)
    X_input = last_sequence_scaled.reshape(1, SEQUENCE_LENGTH, 4)
    
    # Gunakan ensemble jika tersedia
    if use_ensemble and model_lstm_ensemble:
        # Prediksi dari semua model ensemble
        ensemble_predictions = []
        for model in model_lstm_ensemble:
            pred_scaled = model.predict(X_input, verbose=0)
            ensemble_predictions.append(pred_scaled)
        
        # Average predictions (sesuai ensemble_config.json method: "average")
        prediction_scaled = np.mean(ensemble_predictions, axis=0)
    else:
        # Fallback ke single model
        prediction_scaled = model_lstm.predict(X_input, verbose=0)
    
    prediction = scaler_lstm.inverse_transform(prediction_scaled)[0]
    
    # Post-process cahaya: jika prediksi masih dalam ratusan (dari model lama), 
    # clamp ke range yang masuk akal (10-85 lux)
    light_pred = float(prediction[3])
    if light_pred > 100:  # Jika masih dalam ratusan, scale down
        # Scale dari range 100-600 ke 10-85
        # Formula: new_value = 10 + (old_value - 100) * (85 - 10) / (600 - 100)
        light_pred = 10 + (light_pred - 100) * 75 / 500
        light_pred = max(1, min(85, light_pred))  # Clamp ke 1-85 lux
    
    return {
        'amonia_ppm': float(prediction[0]),
        'suhu_c': float(prediction[1]),
        'kelembaban_rh': float(prediction[2]),
        'cahaya_lux': light_pred
    }


@log_performance
def predict_multiple_steps(recent_history, steps=6):
    """
    Prediksi beberapa langkah ke depan (untuk 6 jam dan 24 jam)
    Menggunakan ensemble LSTM jika tersedia, fallback ke single model
    """
    predictions = []
    current_seq = np.array(recent_history[-SEQUENCE_LENGTH:]).copy()
    
    for step in range(steps):
        # Scale sequence
        seq_scaled = scaler_lstm.transform(current_seq)
        X_input = seq_scaled.reshape(1, SEQUENCE_LENGTH, 4)
        
        # Gunakan ensemble jika tersedia
        if use_ensemble and model_lstm_ensemble:
            # Prediksi dari semua model ensemble
            ensemble_predictions = []
            for model in model_lstm_ensemble:
                pred_scaled = model.predict(X_input, verbose=0)
                ensemble_predictions.append(pred_scaled)
            
            # Average predictions (sesuai ensemble_config.json method: "average")
            pred_scaled = np.mean(ensemble_predictions, axis=0)
        else:
            # Fallback ke single model
            pred_scaled = model_lstm.predict(X_input, verbose=0)
        
        pred = scaler_lstm.inverse_transform(pred_scaled)[0]
        
        # Post-process cahaya: jika prediksi masih dalam ratusan (dari model lama), 
        # clamp ke range yang masuk akal (10-85 lux)
        light_pred = float(pred[3])
        if light_pred > 100:  # Jika masih dalam ratusan, scale down
            # Scale dari range 100-600 ke 10-85
            # Formula: new_value = 10 + (old_value - 100) * (85 - 10) / (600 - 100)
            light_pred = 10 + (light_pred - 100) * 75 / 500
            light_pred = max(1, min(85, light_pred))  # Clamp ke 1-85 lux
        
        predictions.append({
            'ammonia': float(pred[0]),
            'temperature': float(pred[1]),
            'humidity': float(pred[2]),
            'light': light_pred
        })
        
        # Update sequence (shift and add prediction)
        current_seq = np.vstack([current_seq[1:], pred])
    
    return predictions


@app.route('/', methods=['GET'])
def index():
    """Root endpoint - Service info"""
    return jsonify({
        'service': 'ML Service for Poultry Farm Monitoring',
        'status': 'running',
        'models_loaded': MODELS_LOADED,
        'endpoints': {
            'health': '/health',
            'predict': '/predict (POST)',
            'classify': '/classify (POST)',
            'anomaly': '/anomaly (POST)'
        },
        'version': '1.0.0',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint dengan informasi model yang lebih detail"""
    try:
        # Cek apakah semua model loaded
        models_loaded = {
            'lstm': model_lstm is not None or (model_lstm_ensemble and len(model_lstm_ensemble) > 0),
            'random_forest': model_rf is not None,
            'isolation_forest': model_if is not None
        }
        
        all_loaded = all(models_loaded.values())
        status = 'healthy' if all_loaded else 'degraded'
        
        return jsonify({
            'status': status,
            'models_loaded': models_loaded,
            'all_models_loaded': all_loaded,
            'timestamp': datetime.now().isoformat()
        }), 200
    except Exception as e:
        logger.error(f'Health check error: {str(e)}', exc_info=True)
        return jsonify({
            'status': 'unhealthy',
            'error': str(e),
            'timestamp': datetime.now().isoformat()
        }), 500


@app.route('/metrics', methods=['GET'])
def get_metrics():
    """Endpoint untuk melihat metrics"""
    try:
        return jsonify({
            'metrics': prediction_metrics,
            'timestamp': datetime.now().isoformat()
        }), 200
    except Exception as e:
        logger.error(f'Metrics error: {str(e)}', exc_info=True)
        return jsonify({
            'error': str(e),
            'timestamp': datetime.now().isoformat()
        }), 500


@app.route('/predict', methods=['POST'])
def predict():
    """
    Main prediction endpoint dengan error handling dan validasi yang komprehensif
    Expected format sesuai ML_INTEGRATION.md
    """
    start_time = time.time()
    prediction_metrics['total_requests'] += 1
    
    # 1. VALIDASI: Cek apakah models loaded
    if not MODELS_LOADED:
        prediction_metrics['failed_predictions'] += 1
        logger.error('Prediction failed: Models not loaded')
        return jsonify({
            'error': 'Models not loaded',
            'message': 'Please check model files'
        }), 500
    
    try:
        # 2. VALIDASI: Cek request body
        if not request.is_json:
            prediction_metrics['failed_predictions'] += 1
            logger.warning('Prediction failed: Content-Type must be application/json')
            return jsonify({
                'error': 'Content-Type must be application/json'
            }), 400
        
        data = request.get_json()
        if not data:
            prediction_metrics['failed_predictions'] += 1
            logger.warning('Prediction failed: Request body is empty')
            return jsonify({
                'error': 'Request body is empty'
            }), 400
        
        # 3. VALIDASI: Cek field 'history'
        if 'history' not in data:
            prediction_metrics['failed_predictions'] += 1
            logger.warning('Prediction failed: Missing required field: history')
            return jsonify({
                'error': 'Missing required field: history'
            }), 400
        
        history = data.get('history', [])
        
        # 4. VALIDASI: Cek jumlah data history
        if not isinstance(history, list):
            prediction_metrics['failed_predictions'] += 1
            logger.warning('Prediction failed: History must be a list/array')
            return jsonify({
                'error': 'History must be a list/array'
            }), 400
        
        if len(history) < SEQUENCE_LENGTH:
            prediction_metrics['failed_predictions'] += 1
            logger.warning(f'Prediction failed: Insufficient history data. Need at least {SEQUENCE_LENGTH} data points, received {len(history)}')
            return jsonify({
                'error': f'History must contain at least {SEQUENCE_LENGTH} data points',
                'received': len(history),
                'required': SEQUENCE_LENGTH
            }), 400
        
        # 5. VALIDASI: Cek format setiap data point
        history_array = []
        for i, h in enumerate(history):
            if not isinstance(h, (list, dict)):
                prediction_metrics['failed_predictions'] += 1
                logger.warning(f'Prediction failed: History entry {i} must be a list or dict')
                return jsonify({
                    'error': f'History entry {i} must be a list or dict',
                    'entry': h
                }), 400
            
            # Convert dict to list jika perlu
            if isinstance(h, dict):
                # Support multiple key formats for backward compatibility
                amonia_val = h.get('ammonia', h.get('amonia_ppm', 0))
                suhu_val = h.get('temperature', h.get('suhu_c', 0))
                kelembaban_val = h.get('humidity', h.get('kelembaban_rh', 0))
                cahaya_val = h.get('light', h.get('cahaya_lux', 0))
                
                # Cek tipe data (harus numeric)
                try:
                    amonia_val = float(amonia_val)
                    suhu_val = float(suhu_val)
                    kelembaban_val = float(kelembaban_val)
                    cahaya_val = float(cahaya_val)
                except (ValueError, TypeError) as e:
                    prediction_metrics['failed_predictions'] += 1
                    logger.warning(f'Prediction failed: History entry {i} contains non-numeric values')
                    return jsonify({
                        'error': f'History entry {i} contains non-numeric values',
                        'entry': h,
                        'details': str(e)
                    }), 400
                
                history_array.append([amonia_val, suhu_val, kelembaban_val, cahaya_val])
            else:
                # List format
                if len(h) != 4:
                    prediction_metrics['failed_predictions'] += 1
                    logger.warning(f'Prediction failed: History entry {i} must have exactly 4 sensor values')
                    return jsonify({
                        'error': f'History entry {i} must have exactly 4 sensor values',
                        'received': len(h)
                    }), 400
                
                # Cek tipe data (harus numeric)
                try:
                    history_array.append([float(v) for v in h])
                except (ValueError, TypeError) as e:
                    prediction_metrics['failed_predictions'] += 1
                    logger.warning(f'Prediction failed: History entry {i} contains non-numeric values')
                    return jsonify({
                        'error': f'History entry {i} contains non-numeric values',
                        'entry': h,
                        'details': str(e)
                    }), 400
        
        # 6. VALIDASI: Convert ke numpy array
        try:
            history_array = np.array(history_array[-SEQUENCE_LENGTH:], dtype=np.float32)
        except (ValueError, TypeError) as e:
            prediction_metrics['failed_predictions'] += 1
            logger.error(f'Prediction failed: Failed to convert history to array: {str(e)}')
            return jsonify({
                'error': 'Failed to convert history to array',
                'details': str(e)
            }), 400
        
        # 7. VALIDASI: Cek range nilai (optional, tapi recommended)
        if np.any(history_array < 0):
            logger.warning(f'Negative values detected in history: {history_array[history_array < 0]}')
        
        # 8. Get latest sensor reading
        latest = history[-1]
        if isinstance(latest, dict):
            amonia = float(latest.get('ammonia', latest.get('amonia_ppm', 0)))
            suhu = float(latest.get('temperature', latest.get('suhu_c', 0)))
            kelembaban = float(latest.get('humidity', latest.get('kelembaban_rh', 0)))
            cahaya = float(latest.get('light', latest.get('cahaya_lux', 0)))
        else:
            amonia = float(latest[0])
            suhu = float(latest[1])
            kelembaban = float(latest[2])
            cahaya = float(latest[3])
        
        # CRITICAL: Ensure the last entry in history_array matches the latest values
        if len(history_array) > 0:
            history_array[-1] = [amonia, suhu, kelembaban, cahaya]
        
        # 9. PREDIKSI dengan error handling
        try:
            # Predict 6 hours ahead
            pred_6h = predict_multiple_steps(history_array, steps=6)
            
            # Predict 24 hours ahead
            pred_24h = predict_multiple_steps(history_array, steps=24)
            
            # Classify current status
            status_result = predict_sensor_classification(amonia, suhu, kelembaban, cahaya)
            
            # Detect anomalies in history
            # Gunakan semua 30 data points yang dikirim (sesuai dengan LSTM sequence length)
            anomalies = []
            for h in history:  # Check all history data (30 data points)
                # Extract values dengan error handling
                if isinstance(h, dict):
                    amonia_val = float(h.get('ammonia', h.get('amonia_ppm', 0)))
                    suhu_val = float(h.get('temperature', h.get('suhu_c', 0)))
                    kelembaban_val = float(h.get('humidity', h.get('kelembaban_rh', 0)))
                    cahaya_val = float(h.get('light', h.get('cahaya_lux', 0)))
                    h_time = h.get('time', '')
                else:
                    amonia_val = float(h[0])
                    suhu_val = float(h[1])
                    kelembaban_val = float(h[2])
                    cahaya_val = float(h[3])
                    h_time = ''
                
                anomaly_result = detect_anomaly(amonia_val, suhu_val, kelembaban_val, cahaya_val)
                
                if anomaly_result['is_anomaly']:
                    # Gunakan detail dari detect_anomaly yang sudah diperbaiki
                    if anomaly_result.get('anomaly_details') and len(anomaly_result['anomaly_details']) > 0:
                        # Ambil detail pertama (atau semua jika perlu)
                        for detail in anomaly_result['anomaly_details']:
                            anomalies.append({
                                'type': detail['sensor'],
                                'value': float(detail['value']),
                                'time': h_time,
                                'message': detail['message'],
                                'status': anomaly_result['status'],
                                'anomaly_score': float(anomaly_result['anomaly_score']),
                                'severity': 'critical' if anomaly_result['anomaly_score'] < -0.5 else 'warning'
                            })
                    else:
                        # Fallback jika tidak ada detail
                        type_msg = 'unknown'
                        message = 'Anomali terdeteksi pada sensor'
                        
                        if amonia_val > 25:
                            type_msg = 'ammonia'
                            message = 'Kadar amoniak tinggi, cek ventilasi'
                        elif suhu_val > 30 or suhu_val < 20:
                            type_msg = 'temperature'
                            message = f'Suhu di luar rentang optimal (20-30°C): {suhu_val:.1f}°C'
                        elif kelembaban_val < 55 or kelembaban_val > 75:
                            type_msg = 'humidity'
                            message = f'Kelembaban di luar rentang optimal (55-75%): {kelembaban_val:.1f}%'
                        elif cahaya_val < 10 or cahaya_val > 60:
                            type_msg = 'light'
                            message = f'Cahaya di luar rentang optimal (10-60 lux): {cahaya_val:.1f} lux'
                        
                        anomalies.append({
                            'type': type_msg,
                            'value': float(amonia_val if type_msg == 'ammonia' else 
                                          suhu_val if type_msg == 'temperature' else
                                          kelembaban_val if type_msg == 'humidity' else cahaya_val),
                            'time': h_time,
                            'message': message,
                            'status': anomaly_result['status'],
                            'anomaly_score': float(anomaly_result['anomaly_score']),
                            'severity': 'critical' if anomaly_result['anomaly_score'] < -0.5 else 'warning'
                        })
            
            # Check anomaly for latest reading (current data)
            latest_anomaly_result = detect_anomaly(amonia, suhu, kelembaban, cahaya)
            if latest_anomaly_result['is_anomaly']:
                # Gunakan detail dari detect_anomaly yang sudah diperbaiki
                if isinstance(latest, dict):
                    latest_time = latest.get('time', '')
                else:
                    latest_time = history[-1].get('time', '') if isinstance(history[-1], dict) else ''
                
                if latest_anomaly_result.get('anomaly_details') and len(latest_anomaly_result['anomaly_details']) > 0:
                    # Tambahkan semua detail anomali untuk latest reading
                    for detail in latest_anomaly_result['anomaly_details']:
                        # Check if this anomaly is already in the list
                        already_exists = any(
                            a.get('time') == latest_time and a.get('type') == detail['sensor']
                            for a in anomalies
                        )
                        
                        if not already_exists:
                            anomalies.append({
                                'type': detail['sensor'],
                                'value': float(detail['value']),
                                'time': latest_time,
                                'message': detail['message'],
                                'status': latest_anomaly_result['status'],
                                'anomaly_score': float(latest_anomaly_result['anomaly_score']),
                                'severity': 'critical' if latest_anomaly_result['anomaly_score'] < -0.5 else 'warning'
                            })
                else:
                    # Fallback jika tidak ada detail
                    type_msg = 'unknown'
                    message = 'Anomali terdeteksi pada sensor saat ini'
                    
                    if amonia > 25:
                        type_msg = 'ammonia'
                        message = 'Kadar amoniak tinggi, cek ventilasi'
                    elif suhu > 30 or suhu < 20:
                        type_msg = 'temperature'
                        message = f'Suhu di luar rentang optimal (20-30°C): {suhu:.1f}°C'
                    elif kelembaban < 55 or kelembaban > 75:
                        type_msg = 'humidity'
                        message = f'Kelembaban di luar rentang optimal (55-75%): {kelembaban:.1f}%'
                    # Untuk cahaya, TIDAK dikonversi - nilai aktual ratusan langsung dibandingkan dengan threshold 10-60
                    if cahaya < 10 or cahaya > 60:
                        type_msg = 'light'
                        message = f'Cahaya di luar rentang optimal (10-60 lux): {cahaya:.1f} lux'
                    
                    # Check if this anomaly is already in the list
                    already_exists = any(
                        a.get('time') == latest_time and a.get('type') == type_msg
                        for a in anomalies
                    )
                    
                    if not already_exists:
                        anomalies.append({
                            'type': type_msg,
                            'value': float(amonia if type_msg == 'ammonia' else 
                                          suhu if type_msg == 'temperature' else
                                          kelembaban if type_msg == 'humidity' else cahaya),
                            'time': latest_time,
                            'message': message,
                            'status': latest_anomaly_result['status'],
                            'anomaly_score': float(latest_anomaly_result['anomaly_score']),
                            'severity': 'critical' if latest_anomaly_result['anomaly_score'] < -0.5 else 'warning'
                        })
            
            prediction_time = int((time.time() - start_time) * 1000)  # in milliseconds
            
            # Generate forecast summaries
            def qualitative_forecast(series, metric, unit, safe_low, safe_high):
                min_val = min(series)
                max_val = max(series)
                trend = series[-1] - series[0]
                dir_str = 'meningkat' if trend > 0.5 else ('menurun' if trend < -0.5 else 'stabil')
                risk = 'potensi keluar batas aman' if (min_val < safe_low or max_val > safe_high) else 'dalam kisaran aman'
                return {
                    'metric': metric,
                    'summary': f"{metric} {dir_str} ({min_val:.2f}–{max_val:.2f} {unit}) {risk}",
                    'range': {'min': round(min_val, 2), 'max': round(max_val, 2), 'unit': unit},
                    'trend': dir_str,
                    'risk': risk
                }
            
            # Threshold untuk cahaya: sesuai aturan boiler (10-60 lux)
            # Catatan: Data aktual mungkin ratusan, tapi threshold tetap 10-60 sesuai aturan boiler
            # Untuk forecast, konversi nilai cahaya dari ratusan ke puluhan (dibagi 10) untuk pengecekan threshold
            def check_light_risk(light_values):
                """Cek apakah nilai cahaya di luar batas aman (threshold 10-60 lux sesuai aturan boiler)"""
                # TIDAK dikonversi - nilai aktual ratusan langsung dibandingkan dengan threshold 10-60
                # Karena nilai aktual ratusan (308.8-369.4) dan threshold 10-60, maka:
                # Jika nilai > 60, berarti "di luar batas aman" (bukan potensi, tapi memang tidak aman)
                if not light_values:
                    return 'tidak diketahui'
                min_val = min(light_values)
                max_val = max(light_values)
                # Threshold: 10-60 lux (sesuai aturan boiler)
                # Nilai aktual ratusan (308.8-369.4) langsung dibandingkan dengan threshold 10-60
                # Jika ada nilai di luar 10-60, maka "di luar batas aman" (bukan potensi, tapi memang tidak aman)
                if min_val < 10 or max_val > 60:
                    return 'di luar batas aman'
                # Jika semua nilai dalam 10-60, tapi ada yang mendekati batas (di luar ideal 20-40), maka "potensi keluar batas aman"
                if min_val < 20 or max_val > 40:
                    return 'potensi keluar batas aman'
                return 'dalam kisaran aman'
            
            # Forecast 6h
            # Catatan: Nilai cahaya tetap dalam ratusan untuk display, tapi threshold tetap 10-60 untuk pengecekan
            light_6h_values = [p['light'] for p in pred_6h]
            light_6h_risk = check_light_risk(light_6h_values)  # Pengecekan menggunakan threshold 10-60
            light_6h_min = min(light_6h_values) if light_6h_values else 0  # Display tetap ratusan
            light_6h_max = max(light_6h_values) if light_6h_values else 0  # Display tetap ratusan
            light_6h_trend = (light_6h_values[-1] - light_6h_values[0]) if len(light_6h_values) > 1 else 0
            light_6h_dir = 'meningkat' if light_6h_trend > 5 else ('menurun' if light_6h_trend < -5 else 'stabil')
            
            # Thresholds sesuai standar boiler dari model_metadata.json
            # Suhu: ideal 23-34°C, danger <23 atau >34°C
            # Kelembaban: ideal 50-70%, warn >80%
            # Amonia: ideal ≤20 ppm, warn >35 ppm
            forecast_6h_summary = [
                qualitative_forecast([p['temperature'] for p in pred_6h], 'Suhu', '°C', 23, 34),  # Sesuai metadata: ideal_min: 23, ideal_max: 34
                qualitative_forecast([p['humidity'] for p in pred_6h], 'Kelembaban', '%', 50, 70),  # Sesuai metadata: ideal_min: 50, ideal_max: 70
                qualitative_forecast([p['ammonia'] for p in pred_6h], 'Amoniak', 'ppm', 0, 20),  # Sesuai metadata: ideal_max: 20
                {
                    'metric': 'Cahaya',
                    'summary': f"Cahaya {light_6h_dir} ({light_6h_min:.1f}–{light_6h_max:.1f} lux) {light_6h_risk}",
                    'range': {'min': round(light_6h_min, 2), 'max': round(light_6h_max, 2), 'unit': 'lux'},
                    'trend': light_6h_dir,
                    'risk': light_6h_risk
                }
            ]
            
            # Forecast 24h
            # Catatan: Nilai cahaya tetap dalam ratusan untuk display, tapi threshold tetap 10-60 untuk pengecekan
            light_24h_values = [p['light'] for p in pred_24h]
            light_24h_risk = check_light_risk(light_24h_values)  # Pengecekan menggunakan threshold 10-60
            light_24h_min = min(light_24h_values) if light_24h_values else 0  # Display tetap ratusan
            light_24h_max = max(light_24h_values) if light_24h_values else 0  # Display tetap ratusan
            light_24h_trend = (light_24h_values[-1] - light_24h_values[0]) if len(light_24h_values) > 1 else 0
            light_24h_dir = 'meningkat' if light_24h_trend > 5 else ('menurun' if light_24h_trend < -5 else 'stabil')
            
            # Thresholds sesuai standar boiler dari model_metadata.json
            forecast_24h_summary = [
                qualitative_forecast([p['temperature'] for p in pred_24h], 'Suhu', '°C', 23, 34),  # Sesuai metadata: ideal_min: 23, ideal_max: 34
                qualitative_forecast([p['humidity'] for p in pred_24h], 'Kelembaban', '%', 50, 70),  # Sesuai metadata: ideal_min: 50, ideal_max: 70
                qualitative_forecast([p['ammonia'] for p in pred_24h], 'Amoniak', 'ppm', 0, 20),  # Sesuai metadata: ideal_max: 20
                {
                    'metric': 'Cahaya',
                    'summary': f"Cahaya {light_24h_dir} ({light_24h_min:.1f}–{light_24h_max:.1f} lux) {light_24h_risk}",
                    'range': {'min': round(light_24h_min, 2), 'max': round(light_24h_max, 2), 'unit': 'lux'},
                    'trend': light_24h_dir,
                    'risk': light_24h_risk
                }
            ]
            
            # Format response sesuai ML_INTEGRATION.md
            # Include latest values in response
            response = {
                'latest': {
                    'time': latest.get('time', '') if isinstance(latest, dict) else '',
                    'temperature': float(suhu),
                    'humidity': float(kelembaban),
                    'ammonia': float(amonia),
                    'light': float(cahaya)
                },
                'prediction_6h': {
                    'temperature': [p['temperature'] for p in pred_6h],
                    'humidity': [p['humidity'] for p in pred_6h],
                    'ammonia': [p['ammonia'] for p in pred_6h],
                    'light': [p['light'] for p in pred_6h]
                },
                'prediction_24h': {
                    'temperature': [p['temperature'] for p in pred_24h],
                    'humidity': [p['humidity'] for p in pred_24h],
                    'ammonia': [p['ammonia'] for p in pred_24h],
                    'light': [p['light'] for p in pred_24h]
                },
                'forecast_summary_6h': forecast_6h_summary,
                'forecast_summary_24h': forecast_24h_summary,
                'anomalies': anomalies,
                'status': {
                    'label': status_result['label'],  # baik, perhatian, buruk (lowercase)
                    'severity': status_result['severity'],
                    'status': status_result['status'],  # BAIK, PERHATIAN, BURUK (uppercase)
                    'probability': status_result['probability'],  # Include probability dict
                    'confidence': float(status_result['confidence']),
                    'message': status_result['message'],
                    'threshold_used': status_result.get('threshold_used', 0.55)
                },
                'anomaly': latest_anomaly_result,  # Include latest anomaly detection
                'ml_metadata': {
                    'model_name': f"{model_metadata.get('project', 'Monitoring Kandang Ayam')} - {rf_info.get('version', 'v1.0')}",
                    'model_version': rf_info.get('version', '1.0'),
                    'accuracy': rf_info.get('accuracy', None),
                    'prediction_time': prediction_time,
                    'confidence': 'high' if status_result['confidence'] > 0.8 else 'medium' if status_result['confidence'] > 0.6 else 'low',
                    'source': 'ml_service'
                },
                'model_name': f"{model_metadata.get('project', 'Monitoring Kandang Ayam')} - {rf_info.get('version', 'v1.0')}",
                'model_version': rf_info.get('version', '1.0'),
                'accuracy': rf_info.get('accuracy', None),
                'prediction_time': prediction_time,
                'confidence': 'high' if status_result['confidence'] > 0.8 else 'medium' if status_result['confidence'] > 0.6 else 'low'
            }
            
            # 10. RESPONSE SUKSES dengan metrics
            prediction_metrics['successful_predictions'] += 1
            processing_time = time.time() - start_time
            
            # Update average processing time
            total_success = prediction_metrics['successful_predictions']
            prediction_metrics['avg_processing_time'] = (
                (prediction_metrics['avg_processing_time'] * (total_success - 1) + processing_time)
                / total_success
            )
            
            logger.info(f'Prediction successful in {processing_time:.3f}s')
            
            # Add processing time to response
            response['processing_time_ms'] = round(processing_time * 1000, 2)
            response['metadata'] = {
                'history_length': len(history),
                'sequence_length_used': SEQUENCE_LENGTH
            }
            
            return jsonify(response), 200
            
        except Exception as e:
            # Error handling untuk prediksi
            prediction_metrics['failed_predictions'] += 1
            logger.error(f'Prediction error: {str(e)}', exc_info=True)
            return jsonify({
                'error': 'Prediction failed',
                'details': str(e) if app.debug else 'Contact administrator'
            }), 500
    
    except Exception as e:
        # Main exception handler for predict function
        prediction_metrics['failed_predictions'] += 1
        logger.error(f'Unexpected error in predict: {str(e)}', exc_info=True)
        return jsonify({
            'error': 'Internal server error',
            'details': str(e) if app.debug else 'Contact administrator'
        }), 500


@app.route('/classify', methods=['POST'])
def classify():
    """Endpoint khusus untuk klasifikasi status real-time"""
    if not MODELS_LOADED:
        return jsonify({'error': 'Models not loaded'}), 500
    
    try:
        data = request.json
        result = predict_sensor_classification(
            data.get('ammonia', data.get('amonia_ppm', 0)),
            data.get('temperature', data.get('suhu_c', 0)),
            data.get('humidity', data.get('kelembaban_rh', 0)),
            data.get('light', data.get('cahaya_lux', 0))
        )
        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/classify', methods=['POST'])
def api_classify():
    """Klasifikasi status kandang (BAIK/PERHATIAN/BURUK) - Compatible dengan struktur baru"""
    if not MODELS_LOADED:
        return jsonify({'success': False, 'error': 'Models not loaded'}), 500
    
    try:
        data = request.json
        amonia = float(data.get('amonia_ppm', data.get('ammonia', 0)))
        suhu = float(data.get('suhu_c', data.get('temperature', 0)))
        kelembaban = float(data.get('kelembaban_rh', data.get('humidity', 0)))
        cahaya = float(data.get('cahaya_lux', data.get('light', 0)))
        
        # Prepare input
        sensor_data = np.array([[amonia, suhu, kelembaban, cahaya]])
        X_scaled = scaler_rf.transform(sensor_data)
        
        # Predict
        prediction = model_rf.predict(X_scaled)[0]
        probabilities = model_rf.predict_proba(X_scaled)[0]
        
        # Map to status sesuai dengan model yang sudah ada
        status_labels_map = {1: 'PERHATIAN', 2: 'BURUK'}
        if prediction not in model_rf.classes_:
            status = 'BAIK'
            status_code = 0
        else:
            status = status_labels_map.get(prediction, 'UNKNOWN')
            status_code = int(prediction)
        
        # Get probabilities
        prob_dict = {}
        for idx, class_label_trained in enumerate(model_rf.classes_):
            if class_label_trained in status_labels_map:
                prob_dict[status_labels_map[class_label_trained]] = float(probabilities[idx])
        
        # Calculate probability for 'BAIK'
        other_probs_sum = sum(prob_dict.values())
        prob_dict['BAIK'] = max(0.0, 1.0 - other_probs_sum)
        
        return jsonify({
            'status': status,
            'probability': prob_dict,
            'success': True
        })
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 400


@app.route('/anomaly', methods=['POST'])
def anomaly():
    """Endpoint khusus untuk deteksi anomali"""
    if not MODELS_LOADED:
        return jsonify({'error': 'Models not loaded'}), 500
    
    try:
        data = request.json
        result = detect_anomaly(
            data.get('ammonia', data.get('amonia_ppm', 0)),
            data.get('temperature', data.get('suhu_c', 0)),
            data.get('humidity', data.get('kelembaban_rh', 0)),
            data.get('light', data.get('cahaya_lux', 0))
        )
        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/detect-anomaly', methods=['POST'])
def api_detect_anomaly():
    """Deteksi anomali pada sensor dengan detail sensor spesifik - Compatible dengan struktur baru"""
    if not MODELS_LOADED:
        return jsonify({'success': False, 'error': 'Models not loaded'}), 500
    
    try:
        data = request.json
        amonia = float(data.get('amonia_ppm', data.get('ammonia', 0)))
        suhu = float(data.get('suhu_c', data.get('temperature', 0)))
        kelembaban = float(data.get('kelembaban_rh', data.get('humidity', 0)))
        cahaya = float(data.get('cahaya_lux', data.get('light', 0)))
        
        # Gunakan fungsi detect_anomaly yang sudah diperbaiki
        result = detect_anomaly(amonia, suhu, kelembaban, cahaya)
        result['success'] = True
        
        return jsonify(result)
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 400

@app.route('/api/predict', methods=['POST'])
def api_predict():
    """Prediksi nilai sensor berikutnya (butuh 30 data history) - Compatible dengan struktur baru"""
    if not MODELS_LOADED:
        return jsonify({'success': False, 'error': 'Models not loaded'}), 500
    
    try:
        data = request.json
        history = data.get('history', [])  # Array of [amonia, suhu, kelembaban, cahaya] atau dict
        
        if len(history) < SEQUENCE_LENGTH:
            return jsonify({
                'success': False,
                'error': f'Need at least {SEQUENCE_LENGTH} historical data points'
            }), 400
        
        # Convert history to array format
        history_array = []
        for h in history[-SEQUENCE_LENGTH:]:
            if isinstance(h, dict):
                # Format: {'ammonia': x, 'temperature': y, ...}
                history_array.append([
                    h.get('ammonia', h.get('amonia_ppm', 0)),
                    h.get('temperature', h.get('suhu_c', 0)),
                    h.get('humidity', h.get('kelembaban_rh', 0)),
                    h.get('light', h.get('cahaya_lux', 0))
                ])
            else:
                # Format: [amonia, suhu, kelembaban, cahaya]
                history_array.append(h)
        
        # Prepare sequence
        seq = np.array(history_array)
        last_sequence_scaled = scaler_lstm.transform(seq)
        X_input = last_sequence_scaled.reshape(1, SEQUENCE_LENGTH, 4)
        
        # Predict
        prediction_scaled = model_lstm.predict(X_input, verbose=0)
        prediction = scaler_lstm.inverse_transform(prediction_scaled)[0]
        
        # Post-process cahaya: jika prediksi masih dalam ratusan (dari model lama), 
        # clamp ke range yang masuk akal (10-85 lux)
        light_pred = float(prediction[3])
        if light_pred > 100:  # Jika masih dalam ratusan, scale down
            # Scale dari range 100-600 ke 10-85
            # Formula: new_value = 10 + (old_value - 100) * (85 - 10) / (600 - 100)
            light_pred = 10 + (light_pred - 100) * 75 / 500
            light_pred = max(1, min(85, light_pred))  # Clamp ke 1-85 lux
        
        return jsonify({
            'amonia_ppm': float(prediction[0]),
            'suhu_c': float(prediction[1]),
            'kelembaban_rh': float(prediction[2]),
            'cahaya_lux': light_pred,
            'success': True
        })
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 400

@app.route('/api/analyze', methods=['POST'])
def api_analyze():
    """Analisis lengkap: klasifikasi + anomali + prediksi - Compatible dengan struktur baru"""
    if not MODELS_LOADED:
        return jsonify({'success': False, 'error': 'Models not loaded'}), 500
    
    try:
        data = request.json
        current = data.get('current', [])  # [amonia, suhu, kelembaban, cahaya]
        history = data.get('history', [])  # Optional: untuk prediksi
        
        result = {}
        
        # Convert current to array if dict
        if isinstance(current, dict):
            current_array = [
                current.get('ammonia', current.get('amonia_ppm', 0)),
                current.get('temperature', current.get('suhu_c', 0)),
                current.get('humidity', current.get('kelembaban_rh', 0)),
                current.get('light', current.get('cahaya_lux', 0))
            ]
        else:
            current_array = current
        
        # 1. Klasifikasi
        sensor_data = np.array([current_array])
        X_scaled_rf = scaler_rf.transform(sensor_data)
        prediction_rf = model_rf.predict(X_scaled_rf)[0]
        probabilities = model_rf.predict_proba(X_scaled_rf)[0]
        
        status_labels_map = {1: 'PERHATIAN', 2: 'BURUK'}
        if prediction_rf not in model_rf.classes_:
            status = 'BAIK'
        else:
            status = status_labels_map.get(prediction_rf, 'UNKNOWN')
        
        prob_dict = {}
        for idx, class_label_trained in enumerate(model_rf.classes_):
            if class_label_trained in status_labels_map:
                prob_dict[status_labels_map[class_label_trained]] = float(probabilities[idx])
        
        other_probs_sum = sum(prob_dict.values())
        prob_dict['BAIK'] = max(0.0, 1.0 - other_probs_sum)
        
        result['classification'] = {
            'status': status,
            'probability': prob_dict
        }
        
        # 2. Anomali
        X_scaled_if = scaler_if.transform(sensor_data)
        prediction_if = model_if.predict(X_scaled_if)[0]
        score = model_if.score_samples(X_scaled_if)[0]
        result['anomaly'] = {
            'is_anomaly': bool(prediction_if == -1),
            'anomaly_score': float(score),
            'status': 'ANOMALI' if prediction_if == -1 else 'NORMAL'
        }
        
        # 3. Prediksi (jika ada history)
        if len(history) >= SEQUENCE_LENGTH:
            # Convert history to array format
            history_array = []
            for h in history[-SEQUENCE_LENGTH:]:
                if isinstance(h, dict):
                    history_array.append([
                        h.get('ammonia', h.get('amonia_ppm', 0)),
                        h.get('temperature', h.get('suhu_c', 0)),
                        h.get('humidity', h.get('kelembaban_rh', 0)),
                        h.get('light', h.get('cahaya_lux', 0))
                    ])
                else:
                    history_array.append(h)
            
            seq = np.array(history_array)
            last_sequence_scaled = scaler_lstm.transform(seq)
            X_input = last_sequence_scaled.reshape(1, SEQUENCE_LENGTH, 4)
            prediction_scaled = model_lstm.predict(X_input, verbose=0)
            prediction = scaler_lstm.inverse_transform(prediction_scaled)[0]
            result['prediction'] = {
                'amonia_ppm': float(prediction[0]),
                'suhu_c': float(prediction[1]),
                'kelembaban_rh': float(prediction[2]),
                'cahaya_lux': float(prediction[3])
            }
        else:
            result['prediction'] = None
        
        result['success'] = True
        return jsonify(result)
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 400


if __name__ == '__main__':
    print("🚀 Starting ML Service for Poultry Farm Monitoring...")
    print(f"📁 Model directory: {MODEL_DIR}")
    print(f"✅ Models loaded: {MODELS_LOADED}")
    app.run(host='0.0.0.0', port=5000, debug=True)

