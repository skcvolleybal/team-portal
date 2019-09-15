ionic cordova build android --prod --release
jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore teamportal.keystore platforms/android/app/build/outputs/apk/release/app-release-unsigned.apk TeamPortal
rm TeamPortal.apk
zipalign -v 4 platforms/android/app/build/outputs/apk/release/app-release-unsigned.apk TeamPortal.apk