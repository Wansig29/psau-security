<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Register New Vehicle') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-maroon-800 mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-maroon-800 mb-4">Vehicle Information</h3>
                    <p class="text-sm text-gray-600 mb-6">Please provide the necessary details and upload a scanned or photographed copy of your Official Receipt (OR) and Certificate of Registration (CR). Our system will automatically extract the necessary information to expedite the process.</p>

                    <form method="POST" action="{{ route('user.registration.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Vehicle Make -->
                        <div class="mt-4">
                            <x-input-label for="make" :value="__('Vehicle Make (e.g. Toyota, Honda)')" />
                            <x-text-input id="make" class="block mt-1 w-full" type="text" name="make" :value="old('make')" required autofocus />
                            <x-input-error :messages="$errors->get('make')" class="mt-2" />
                        </div>

                        <!-- Vehicle Model -->
                        <div class="mt-4">
                            <x-input-label for="model" :value="__('Vehicle Model (e.g. Civic, Vios)')" />
                            <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model')" required />
                            <x-input-error :messages="$errors->get('model')" class="mt-2" />
                        </div>

                        <!-- Vehicle Color -->
                        <div class="mt-4">
                            <x-input-label for="color" :value="__('Vehicle Color')" />
                            <x-text-input id="color" class="block mt-1 w-full" type="text" name="color" :value="old('color')" required />
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>

                        <!-- Vehicle Photo -->
                        <div class="mt-4">
                            <x-input-label for="photo_path" :value="__('Vehicle Photo (Front/Side Profile)')" />
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mb-1 mt-1 p-2" id="photo_path" name="photo_path" type="file" accept="image/*" required>
                            <p class="text-xs text-gray-500">A clear photo of the vehicle showing its color and license plate.</p>
                            <x-input-error :messages="$errors->get('photo_path')" class="mt-2" />
                        </div>

                        <hr class="my-6">

                        <h3 class="text-lg font-bold text-maroon-800 mb-4">Required Documents</h3>

                        <!-- Target File Upload -->
                        <div class="mt-4">
                            <x-input-label for="document_image" :value="__('Upload OR/CR Document Image (JPEG, PNG)')" />
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mb-1 mt-1 p-2" id="document_image" name="document_image" type="file" accept="image/*" required>
                            <p class="text-xs text-gray-500">Ensure the plate number and details are clearly visible for OCR extraction.</p>
                            <x-input-error :messages="$errors->get('document_image')" class="mt-2" />
                        </div>

                        <!-- Certificate of Registration (COR) / Student ID Upload -->
                        <div class="mt-4">
                            <x-input-label for="cor_image" :value="__('Upload Certificate of Registration (COR) / Student ID')" />
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mb-1 mt-1 p-2" id="cor_image" name="cor_image" type="file" accept="image/*,application/pdf" required>
                            <p class="text-xs text-gray-500">Required to validate your status as an active student/personnel of the campus.</p>
                            <x-input-error :messages="$errors->get('cor_image')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500 mr-4" href="{{ route('dashboard') }}">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="bg-maroon-800 hover:bg-maroon-700">
                                {{ __('Submit Registration') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
