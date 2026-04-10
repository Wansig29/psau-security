<x-guest-layout>
    @php
        $apkPath     = public_path('psau_parking.apk');
        $apkExists   = file_exists($apkPath);
        $apkSize     = $apkExists ? round(filesize($apkPath) / 1048576, 1) . ' MB' : null;
        $apkModified = $apkExists ? date('F j, Y', filemtime($apkPath)) : null;
        $downloadUrl = $apkExists ? asset('psau_parking.apk') : null;
    @endphp

    <div class="max-w-md mx-auto">

        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-100 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.5l-7-7 1.41-1.41L11 15.67V3h2v12.67l4.59-4.58L19 12.5l-7 7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Install PSAU Parking App') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Android APK — Direct installation') }}</p>
        </div>

        @if ($apkExists)
            {{-- APK Info Card --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">psau_parking.apk</p>
                        <p class="text-xs text-gray-500">{{ $apkSize }} &middot; Updated {{ $apkModified }}</p>
                    </div>
                </div>
                <div class="px-5 py-4">
                    <a
                        href="{{ $downloadUrl }}"
                        download="psau_parking.apk"
                        class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 active:scale-95 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                        </svg>
                        {{ __('Download APK') }}
                    </a>
                </div>
            </div>

            {{-- Installation Steps --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
                <h2 class="text-sm font-semibold text-amber-800 mb-3">{{ __('How to install') }}</h2>
                <ol class="space-y-2 text-sm text-amber-700 list-none">
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-200 text-amber-800 text-xs font-bold mt-0.5">1</span>
                        {{ __('Tap "Download APK" above to save the file.') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-200 text-amber-800 text-xs font-bold mt-0.5">2</span>
                        {{ __('Open the downloaded file from your notifications or Downloads folder.') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-200 text-amber-800 text-xs font-bold mt-0.5">3</span>
                        {{ __('Tap "Install" and allow Unknown Sources if prompted in Settings.') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-200 text-amber-800 text-xs font-bold mt-0.5">4</span>
                        {{ __('Open the PSAU Parking app and log in with your account.') }}
                    </li>
                </ol>
            </div>

        @else
            {{-- APK Not Found --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gray-100 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-800">{{ __('APK not available yet') }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Please contact your administrator.') }}</p>
                    <p class="mt-3 text-xs text-gray-400 font-mono bg-gray-50 rounded-lg px-3 py-2">
                        {{ $apkPath }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Back to Login --}}
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                &larr; {{ __('Back to Login') }}
            </a>
        </div>
    </div>
</x-guest-layout>
