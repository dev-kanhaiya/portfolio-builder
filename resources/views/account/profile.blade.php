@extends('layouts.app')

@section('title', 'Account settings | Devfolio')

@section('content')
<section class="account-page wrap">
    <div class="account-page-heading">
        <div>
            <span class="eyebrow">YOUR ACCOUNT</span>
            <h1>Profile settings</h1>
            <p>Keep your details and account photo up to date.</p>
        </div>
        <a class="button button-light" href="{{ route('dashboard') }}">Back to portfolios</a>
    </div>

    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="account-form" id="profile-details-form">
        @csrf
        @method('PUT')

        <section class="account-section">
            <div class="account-section-heading">
                <span class="account-section-icon">◉</span>
                <div><h2>Personal details</h2><p>This is the name and photo shown in your account menu.</p></div>
            </div>

            <div class="profile-photo-row">
                <div class="profile-avatar-large" id="profile-avatar-preview">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="profile-photo-copy">
                    <label class="button button-light button-small" for="profile_photo">Choose a photo</label>
                    <input class="profile-photo-input" id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp">
                    <p>JPG, PNG, or WebP. Large photos are resized automatically.</p>
                    <small class="photo-upload-status" id="photo-upload-status" aria-live="polite"></small>
                </div>
            </div>

            <div class="account-fields-grid">
                <div class="field"><label for="name">Full name</label><input class="input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"></div>
                <div class="field"><label for="email">Email address</label><input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email"></div>
                <div class="field"><label for="phone">Phone number <span class="optional-label">Optional</span></label><input class="input" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel"></div>
                <div class="field"><label for="location">Location <span class="optional-label">Optional</span></label><input class="input" id="location" name="location" type="text" value="{{ old('location', $user->location) }}" autocomplete="address-level2"></div>
            </div>
        </section>

        <div class="account-form-actions">
            <span>Your account information stays private.</span>
            <button class="button" id="profile-save-button" type="submit">Save profile <span>✓</span></button>
        </div>
    </form>

    <form method="POST" action="{{ route('account.password.update') }}" class="account-form password-account-form">
        @csrf
        @method('PUT')
        <section class="account-section password-section">
            <div class="account-section-heading">
                <span class="account-section-icon password-icon">⌑</span>
                <div><h2>Change password</h2><p>Enter your current password to set a new one.</p></div>
            </div>
            <div class="account-fields-grid">
                <div class="field"><label for="current_password">Current password</label><input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password"></div>
                <div class="field"><label for="password">New password</label><input class="input" id="password" name="password" type="password" minlength="8" autocomplete="new-password"><small>Use at least 8 characters.</small></div>
                <div class="field"><label for="password_confirmation">Confirm new password</label><input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
            </div>
        </section>

        <div class="account-form-actions">
            <span>Password changes are saved separately from your profile.</span>
            <button class="button" type="submit">Update password <span>✓</span></button>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
    const profileForm = document.getElementById('profile-details-form');
    const photoInput = document.getElementById('profile_photo');
    const photoPreview = document.getElementById('profile-avatar-preview');
    const photoStatus = document.getElementById('photo-upload-status');
    const profileSaveButton = document.getElementById('profile-save-button');
    const originalPhotoPreview = photoPreview.innerHTML;
    const maxPhotoSize = 1200 * 1024;

    function showPhotoPreview(file) {
        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = 'Profile photo preview';
        photoPreview.replaceChildren(image);
    }

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            const image = new Image();
            const imageUrl = URL.createObjectURL(file);
            image.onload = function () {
                URL.revokeObjectURL(imageUrl);
                resolve(image);
            };
            image.onerror = function () {
                URL.revokeObjectURL(imageUrl);
                reject(new Error('This image could not be opened.'));
            };
            image.src = imageUrl;
        });
    }

    function canvasBlob(canvas, type, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(resolve, type, quality);
        });
    }

    async function compressPhoto(file) {
        const image = await loadImage(file);
        const largestSide = Math.max(image.width, image.height);

        for (const scale of [Math.min(1, 1200 / largestSide), Math.min(1, 900 / largestSide), Math.min(1, 700 / largestSide)]) {
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.width * scale));
            canvas.height = Math.max(1, Math.round(image.height * scale));
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

            for (const quality of [0.82, 0.68, 0.54, 0.4]) {
                let blob = await canvasBlob(canvas, 'image/webp', quality);
                let extension = 'webp';

                if (!blob || blob.type !== 'image/webp') {
                    blob = await canvasBlob(canvas, 'image/jpeg', quality);
                    extension = 'jpg';
                }

                if (blob && blob.size <= maxPhotoSize) {
                    const baseName = file.name.replace(/\.[^.]+$/, '');
                    return new File([blob], baseName + '.' + extension, { type: blob.type });
                }
            }
        }

        return null;
    }

    photoInput.addEventListener('change', async function () {
        const selectedFile = photoInput.files[0];
        if (!selectedFile) return;

        photoStatus.textContent = 'Preparing your photo…';
        photoStatus.classList.remove('photo-upload-error');

        if (selectedFile.size <= maxPhotoSize) {
            showPhotoPreview(selectedFile);
            photoStatus.textContent = 'Photo ready to save.';
            return;
        }

        profileSaveButton.disabled = true;

        try {
            const compressedFile = await compressPhoto(selectedFile);
            if (!compressedFile) throw new Error('Please choose a smaller photo.');

            const updatedFiles = new DataTransfer();
            updatedFiles.items.add(compressedFile);
            photoInput.files = updatedFiles.files;
            showPhotoPreview(compressedFile);
            photoStatus.textContent = 'Photo resized and ready to save.';
        } catch (error) {
            photoInput.value = '';
            photoPreview.innerHTML = originalPhotoPreview;
            photoStatus.textContent = error.message || 'Could not prepare this photo. Please choose another one.';
            photoStatus.classList.add('photo-upload-error');
        }

        profileSaveButton.disabled = false;
    });

    profileForm.addEventListener('submit', function (event) {
        const selectedFile = photoInput.files[0];
        if (selectedFile && selectedFile.size > maxPhotoSize) {
            event.preventDefault();
            photoStatus.textContent = 'Please choose the photo again so it can be resized before upload.';
            photoStatus.classList.add('photo-upload-error');
        }
    });
</script>
@endpush
