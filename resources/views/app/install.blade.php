<x-guest-layout>
    <div class="max-w-xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ __('Install PSAU Parking App') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('Download the Android APK and install it on your phone (non-Play Store distribution).') }}
            </p>
        </div>

        @if (!empty($downloadUrl))
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <div class="text-sm text-gray-700 mb-3">
                    {{ __('Download APK from the configured URL:') }}
                </div>
                <a
                    href="{{ $downloadUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    {{ __('Download APK') }}
                </a>
            </div>
        @else
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <div class="text-sm text-gray-700">
                    {{ __('APK not found on the server yet.') }}
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    {{ __('Place your built APK at:') }}
                    <span class="font-mono text-xs text-gray-500">public/psau_parking.apk</span>
                </div>
            </div>
        @endif

        <div class="mt-5 text-xs text-gray-500">
            {{ __('Tip: After downloading, open the APK file on Android and allow installation if prompted.') }}
        </div>
    </div>
</x-guest-layout>

