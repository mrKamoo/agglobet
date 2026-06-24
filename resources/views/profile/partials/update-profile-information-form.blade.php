<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Photo de profil -->
        <div class="flex items-center space-x-6">
            <div class="shrink-0 relative group">
                <img class="h-16 w-16 object-cover rounded-full border-2 border-indigo-150 shadow-sm" src="{{ $user->avatar_url }}" alt="Avatar actuel" id="avatar-preview">
            </div>
            <div class="flex-1 space-y-2">
                <x-input-label for="avatar" :value="__('Photo de profil')" />
                <input id="avatar" name="avatar" type="file" class="hidden" accept="image/*" onchange="previewImage(event)" />
                <div class="flex space-x-2">
                    <label for="avatar" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Choisir une image') }}
                    </label>
                    @if($user->avatar)
                        <button type="button" onclick="deleteAvatar()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Supprimer') }}
                        </button>
                    @endif
                </div>
                <input type="hidden" name="delete_avatar" id="delete_avatar_input" value="0" />
                <p class="text-xs text-gray-500">Formats acceptés : PNG, JPG, GIF ou WEBP. Maximum 2 Mo.</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <script>
            function previewImage(event) {
                const reader = new FileReader();
                reader.onload = function() {
                    const output = document.getElementById('avatar-preview');
                    output.src = reader.result;
                }
                reader.readAsDataURL(event.target.files[0]);
                document.getElementById('delete_avatar_input').value = '0';
            }

            function deleteAvatar() {
                if (confirm('Voulez-vous vraiment supprimer votre photo de profil ?')) {
                    document.getElementById('delete_avatar_input').value = '1';
                    document.getElementById('avatar-preview').src = 'https://api.dicebear.com/7.x/initials/svg?seed=' + encodeURIComponent('{{ $user->name }}') + '&backgroundType=solid,gradientLinear';
                    document.getElementById('avatar').value = '';
                }
            }
        </script>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
