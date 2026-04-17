# PSAU Parking System — Security Module

### 📌 QUICK ACCESS: Flutter Path
If you need to run Flutter commands manually, use this path:
`C:\Users\Janssen\.puro\envs\stable\flutter\bin\flutter.bat`

---

## 🚀 Building the Mobile App (Flutter)

The easiest way to build the app is to use the script in this folder:

1. Open a terminal in this root folder (`psau-security`).
2. Run:
   ```powershell
   .\build_apk.bat
   ```

### Manual Build Commands
If the script above fails, run these in order:

1. `cd psau_parking_system`
2. `C:\Users\Janssen\.puro\envs\stable\flutter\bin\flutter.bat pub get`
3. `C:\Users\Janssen\.puro\envs\stable\flutter\bin\flutter.bat build apk --release`

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
