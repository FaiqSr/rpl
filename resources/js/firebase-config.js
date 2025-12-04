import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup } from 'firebase/auth';
import { getAnalytics } from 'firebase/analytics';

// Firebase configuration
// Can use environment variables or direct config
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY || "AIzaSyCP4Ro5DqqCPsjhQwoKVBjp_peucxFZmWM",
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || "apps-768d1.firebaseapp.com",
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || "apps-768d1",
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || "apps-768d1.firebasestorage.app",
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || "993952133870",
    appId: import.meta.env.VITE_FIREBASE_APP_ID || "1:993952133870:web:1bbb4a6205b67afb1139ff",
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || "G-GSQ22C7RL4"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const googleProvider = new GoogleAuthProvider();

// Initialize Analytics (only in browser environment)
let analytics = null;
if (typeof window !== 'undefined') {
    try {
        analytics = getAnalytics(app);
    } catch (error) {
        console.warn('Firebase Analytics initialization failed:', error);
    }
}

// Make available globally for use in Blade templates
if (typeof window !== 'undefined') {
    window.firebaseAuth = auth;
    window.firebaseProvider = googleProvider;
    window.signInWithPopup = signInWithPopup;
    if (analytics) {
        window.firebaseAnalytics = analytics;
    }
}

