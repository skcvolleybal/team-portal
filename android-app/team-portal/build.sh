ionic cordova build android --prod --release
keytool -genkey -v -keystore teamportal.keystore -alias TeamPortal -keyalg RSA -keysize 2048 -validity 10000
jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore teamportal.keystore platforms/android/app/build/outputs/apk/release/app-release-unsigned.apk TeamPortal
zipalign -v 4 platforms/android/app/build/outputs/apk/release/app-release-unsigned.apk TeamPortal.apk