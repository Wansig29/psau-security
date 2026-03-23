<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log Vehicle Violation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($vehicle)
            <div class="bg-gray-100 p-4 rounded-t-lg border-b mb-0">
                <h3 class="font-bold text-gray-700">Logging Violation For: <span class="text-maroon-800 font-mono">{{ $vehicle->plate_number }}</span></h3>
                <p class="text-sm text-gray-600">{{ $vehicle->make }} {{ $vehicle->model }} - Owner: {{ $vehicle->user->name }}</p>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg border-t-4 border-red-600 mb-6">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('security.violation.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle_id }}">
                        <input type="hidden" name="registration_id" value="{{ $registration_id }}">
                        
                        <!-- Hidden GPS Fields -->
                        <input type="hidden" name="gps_lat" id="gps_lat">
                        <input type="hidden" name="gps_lng" id="gps_lng">

                        <!-- GPS Status Indicator -->
                        <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-gray-700">Location Tagging</h4>
                                <p class="text-xs text-gray-500">Capture exact coordinates for the security map.</p>
                            </div>
                            <div id="gps-status" class="flex items-center text-sm font-medium text-amber-600 bg-amber-100 px-3 py-1 rounded-full border border-amber-300">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Acquiring GPS...
                            </div>
                        </div>

                        <!-- Violation Type -->
                        <div class="mt-4">
                            <x-input-label for="violation_type" :value="__('Violation Type')" />
                            <select id="violation_type" name="violation_type" class="border-gray-300 focus:border-maroon-500 focus:ring-maroon-500 rounded-md shadow-sm block mt-1 w-full" required>
                                <option value="" disabled selected>Select an Offense...</option>
                                <option value="no_sticker">No Valid PSAU Sticker</option>
                                <option value="illegal_parking">Illegal Parking</option>
                                <option value="blocking_driveway">Blocking Driveway/Entrance</option>
                                <option value="speeding">Speeding on Campus</option>
                                <option value="reckless_driving">Reckless Driving</option>
                                <option value="unregistered_no_license">⚠️ Unregistered Vehicle + No Driver's License</option>
                                <option value="other">Other Offense</option>
                            </select>
                            <x-input-error :messages="$errors->get('violation_type')" class="mt-2" />
                        </div>

                        <!-- Location & Notes -->
                        <div class="mt-4">
                            <x-input-label for="location_notes" :value="__('Location & Specific Notes')" />
                            <textarea id="location_notes" name="location_notes" rows="3" class="border-gray-300 focus:border-maroon-500 focus:ring-maroon-500 rounded-md shadow-sm block mt-1 w-full" placeholder="e.g. Parked at the Faculty Only zone near Building A..." required>{{ old('location_notes') }}</textarea>
                            <x-input-error :messages="$errors->get('location_notes')" class="mt-2" />
                        </div>

                        <!-- Photo Evidence -->
                        <div class="mt-4">
                            <x-input-label for="photo_image" :value="__('Attach Photo Evidence (Optional)')" />
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mb-1 mt-1 p-2" id="photo_image" name="photo_image" type="file" accept="image/*">
                            <p class="text-xs text-gray-500">A picture showing the vehicle and the violation context.</p>
                            <x-input-error :messages="$errors->get('photo_image')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a class="text-sm text-gray-600 hover:text-gray-900 mr-4" href="{{ route('security.dashboard') }}">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="bg-red-600 hover:bg-red-700">
                                {{ __('Submit Violation Record') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusEl = document.getElementById('gps-status');
        const latInput = document.getElementById('gps_lat');
        const lngInput = document.getElementById('gps_lng');

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Success callback
                    latInput.value = position.coords.latitude;
                    lngInput.value = position.coords.longitude;
                    
                    statusEl.className = 'flex items-center text-sm font-medium text-green-700 bg-green-100 px-3 py-1 rounded-full border border-green-400';
                    statusEl.innerHTML = `
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Location Acquired
                    `;
                },
                function(error) {
                    // Error callback
                    console.warn("Geolocation Error:", error.message);
                    statusEl.className = 'flex items-center text-sm font-medium text-red-700 bg-red-100 px-3 py-1 rounded-full border border-red-400';
                    statusEl.innerHTML = `
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        GPS Unavailable
                    `;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            statusEl.className = 'flex items-center text-sm font-medium text-gray-700 bg-gray-100 px-3 py-1 rounded-full border border-gray-400';
            statusEl.innerHTML = 'GPS Not Supported';
        }
    });
</script>
