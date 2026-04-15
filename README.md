# PSAU Parking System - Security Module

This project consists of a Laravel-based backend and a Flutter-based mobile application.

## 🚀 Building the Mobile App (Flutter)

If you have Flutter installed via **Puro**, you can build the APK using the provided batch file:

1. Open a terminal in the project root.
2. Run the build script:
   ```powershell
   .\build_apk.bat
   ```

### Manual Build Instructions

If `flutter` is not in your PATH, use the full path to your Flutter executable:

1. Go to the app directory:
   ```powershell
   cd psau_parking_system
   ```
2. Run the build command:
   ```powershell
   C:\Users\Janssen\.puro\envs\stable\flutter\bin\flutter.bat build apk --release
   ```
3. The APK will be generated at `build/app/outputs/flutter-apk/app-release.apk`.

---

## 🛠 Backend (Laravel)

The backend provides the API and admin dashboard.

- **Requirements:** PHP 8.1+, Composer, MySQL.
- **Setup:**
  1. `composer install`
  2. `cp .env.example .env`
  3. `php artisan key:generate`
  4. `php artisan migrate`

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
