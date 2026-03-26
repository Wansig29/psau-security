<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contact Number (PH format) -->
        <div class="mt-4">
            <x-input-label for="contact_number" :value="__('Phone Number')" />
            <x-text-input id="contact_number"
                          class="block mt-1 w-full"
                          type="tel"
                          name="contact_number"
                          :value="old('contact_number')"
                          placeholder="09XXXXXXXXX or +639XXXXXXXXX"
                          maxlength="13"
                          pattern="(09\d{9}|\+639\d{9})"
                          autocomplete="tel"
                          oninput="enforcePHPhone(this)" />
            <p class="mt-1 text-xs text-gray-500">Format: 09XXXXXXXXX (11 digits) or +639XXXXXXXXX</p>
            <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <button type="button" onclick="togglePwd('password','eye-reg-pwd')"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                    tabindex="-1" aria-label="Show/hide password">
                    <svg id="eye-reg-pwd" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <x-text-input id="password_confirmation" class="block w-full pr-10"
                                type="password"
                                name="password_confirmation"
                                required autocomplete="new-password" />
                <button type="button" onclick="togglePwd('password_confirmation','eye-reg-conf')"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                    tabindex="-1" aria-label="Show/hide confirm password">
                    <svg id="eye-reg-conf" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function togglePwd(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            icon.style.opacity = showing ? '1' : '0.45';
        }

        function enforcePHPhone(input) {
            // Allow digits, leading +, allow user to type +63 or 09 prefix
            let v = input.value;
            // Strip anything that isn't digit or + at start
            v = v.replace(/(?!^\+)[^\d]/g, '');
            // Limit length: +639XXXXXXXXX = 13 chars, 09XXXXXXXXX = 11 chars
            if (v.startsWith('+')) {
                input.value = v.slice(0, 13);
            } else {
                input.value = v.slice(0, 11);
            }
        }
    </script>
</x-guest-layout>
