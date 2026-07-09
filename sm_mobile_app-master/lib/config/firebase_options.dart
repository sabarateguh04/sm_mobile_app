// File ini menggantikan kebutuhan FlutterFire CLI
// Generated manually berdasarkan google-services.json
// project_id: sm-mobile-djalu
// package_name: com.example.mobile_sm

import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) return web;
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions not supported for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyDkV-e_cwwzwlON0c4KcCib2KnoKTGnguE',
    appId: '1:1079913631425:web:1234567890abcdef',
    messagingSenderId: '1079913631425',
    projectId: 'sm-mobile-djalu',
    authDomain: 'sm-mobile-djalu.firebaseapp.com',
    storageBucket: 'sm-mobile-djalu.firebasestorage.app',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyDkV-e_cwwzwlON0c4KcCib2KnoKTGnguE',
    appId: '1:1079913631425:android:a93fdbe5a314aec0518e40',
    messagingSenderId: '1079913631425',
    projectId: 'sm-mobile-djalu',
    storageBucket: 'sm-mobile-djalu.firebasestorage.app',
  );
}
