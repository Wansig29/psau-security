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
