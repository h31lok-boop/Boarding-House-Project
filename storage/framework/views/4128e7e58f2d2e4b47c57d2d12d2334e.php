<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => 'Profile',
    'name' => 'profile_image',
    'initial' => '',
    'maxSizeKb' => 2048,
    'fallback' => '',
    'size' => 128,
    'circle' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'label' => 'Profile',
    'name' => 'profile_image',
    'initial' => '',
    'maxSizeKb' => 2048,
    'fallback' => '',
    'size' => 128,
    'circle' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $uploaderId = 'profile-uploader-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8));
    $labelText = trim((string) $label);
    $initialValue = is_string($initial) ? trim($initial) : '';
    $fallbackValue = trim((string) $fallback) !== '' ? trim((string) $fallback) : asset('images/avatar-placeholder.svg');
    $maxBytes = max((int) $maxSizeKb, 1) * 1024;
    $avatarSizePx = max((int) $size, 48);
    $avatarRadiusClass = filter_var($circle, FILTER_VALIDATE_BOOLEAN) ? 'rounded-full' : 'rounded-xl';
    $placeholderIconSizePx = max(min((int) round($avatarSizePx * 0.45), 56), 24);
    $maxSizeLabel = $maxSizeKb >= 1024
        ? rtrim(rtrim(number_format($maxSizeKb / 1024, 2), '0'), '.').' MB'
        : $maxSizeKb.' KB';
?>

<div
    id="<?php echo e($uploaderId); ?>"
    data-profile-uploader
    data-max-bytes="<?php echo e($maxBytes); ?>"
    class="space-y-3"
>
    <?php if($labelText !== ''): ?>
        <label class="block text-sm font-medium text-gray-700"><?php echo e($labelText); ?></label>
    <?php endif; ?>

    <div class="flex flex-wrap items-start gap-4">
        <button
            type="button"
            data-profile-trigger
            class="shrink-0 overflow-hidden border border-gray-300 bg-gray-100 p-0 transition hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 <?php echo e($avatarRadiusClass); ?>"
            style="width: <?php echo e($avatarSizePx); ?>px; height: <?php echo e($avatarSizePx); ?>px;"
            aria-label="Choose profile image"
        >
            <img
                src="<?php echo e($initialValue); ?>"
                alt="Profile preview"
                data-profile-preview
                data-fallback="<?php echo e($fallbackValue); ?>"
                class="block object-cover <?php echo e($initialValue !== '' ? '' : 'hidden'); ?>"
                style="width: 100%; height: 100%;"
            >
            <div
                data-profile-placeholder
                class="flex items-center justify-center <?php echo e($initialValue !== '' ? 'hidden' : ''); ?>"
                style="width: 100%; height: 100%;"
            >
                <svg class="text-gray-400" style="width: <?php echo e($placeholderIconSizePx); ?>px; height: <?php echo e($placeholderIconSizePx); ?>px;" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
                </svg>
            </div>
        </button>

        <div class="space-y-2">
            <input
                type="file"
                name="<?php echo e($name); ?>"
                accept="image/*,.jpg,.jpeg,.png,.webp"
                class="hidden"
                data-profile-input
            >
            <input type="hidden" name="<?php echo e($name); ?>_remove" value="0" data-profile-remove-flag>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    data-profile-replace
                    class="px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    <?php echo e($initialValue !== '' ? 'Replace' : 'Upload'); ?>

                </button>
                <button
                    type="button"
                    data-profile-remove
                    class="px-3 py-1.5 rounded-md border border-rose-200 bg-rose-50 text-sm font-medium text-rose-600 hover:bg-rose-100"
                >
                    Remove
                </button>
            </div>

            <p class="text-xs text-gray-500">Allowed: JPG, PNG, WEBP. Max size: <?php echo e($maxSizeLabel); ?>.</p>
            <p class="hidden text-xs text-rose-600" data-profile-error></p>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('1753f0ed-ba90-4a93-8d30-32d8f1b48806')): $__env->markAsRenderedOnce('1753f0ed-ba90-4a93-8d30-32d8f1b48806'); ?>
    <script>
        (() => {
            const initProfileUploaders = () => {
                document.querySelectorAll('[data-profile-uploader]').forEach((uploader) => {
                    if (uploader.dataset.profileUploaderReady === '1') return;
                    uploader.dataset.profileUploaderReady = '1';

                    const trigger = uploader.querySelector('[data-profile-trigger]');
                    const replaceBtn = uploader.querySelector('[data-profile-replace]');
                    const removeBtn = uploader.querySelector('[data-profile-remove]');
                    const preview = uploader.querySelector('[data-profile-preview]');
                    const placeholder = uploader.querySelector('[data-profile-placeholder]');
                    const input = uploader.querySelector('[data-profile-input]');
                    const errorEl = uploader.querySelector('[data-profile-error]');
                    const removeFlag = uploader.querySelector('[data-profile-remove-flag]');

                    if (!trigger || !replaceBtn || !removeBtn || !preview || !placeholder || !input || !errorEl || !removeFlag) {
                        return;
                    }

                    const maxBytes = Number(uploader.dataset.maxBytes || 0);
                    const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
                    const allowedPattern = /\.(jpe?g|png|webp)$/i;
                    let tempUrl = '';
                    const fallbackSrc = preview.dataset.fallback || '';

                    const revokeTemp = () => {
                        if (!tempUrl) return;
                        URL.revokeObjectURL(tempUrl);
                        tempUrl = '';
                    };

                    const setReplaceLabel = (hasSource) => {
                        replaceBtn.textContent = hasSource ? 'Replace' : 'Upload';
                    };

                    const setPreview = (src) => {
                        const hasSource = typeof src === 'string' && src.trim() !== '';
                        if (hasSource) {
                            preview.src = src;
                            preview.classList.remove('hidden');
                            placeholder.classList.add('hidden');
                            removeBtn.disabled = false;
                            removeBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                            setReplaceLabel(true);
                            return;
                        }

                        preview.removeAttribute('src');
                        preview.classList.add('hidden');
                        placeholder.classList.remove('hidden');
                        removeBtn.disabled = true;
                        removeBtn.classList.add('opacity-60', 'cursor-not-allowed');
                        setReplaceLabel(false);
                    };

                    const clearError = () => {
                        errorEl.textContent = '';
                        errorEl.classList.add('hidden');
                    };

                    const showError = (message) => {
                        errorEl.textContent = message;
                        errorEl.classList.remove('hidden');
                    };

                    const openPicker = () => input.click();

                    trigger.addEventListener('click', openPicker);
                    replaceBtn.addEventListener('click', openPicker);

                    removeBtn.addEventListener('click', () => {
                        revokeTemp();
                        input.value = '';
                        removeFlag.value = '1';
                        clearError();
                        setPreview('');
                    });

                    preview.addEventListener('error', () => {
                        if (fallbackSrc && preview.getAttribute('src') !== fallbackSrc) {
                            preview.src = fallbackSrc;
                            preview.classList.remove('hidden');
                            placeholder.classList.add('hidden');
                            removeBtn.disabled = false;
                            removeBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                            setReplaceLabel(true);
                            return;
                        }

                        setPreview('');
                    });

                    input.addEventListener('change', () => {
                        const file = input.files && input.files[0];
                        if (!file) return;

                        const isAllowedType = allowedTypes.has(file.type) || allowedPattern.test(file.name || '');
                        if (!isAllowedType) {
                            input.value = '';
                            showError('Invalid file type. Please upload JPG, PNG, or WEBP.');
                            return;
                        }

                        if (maxBytes > 0 && file.size > maxBytes) {
                            input.value = '';
                            showError('File is too large. Please choose a smaller image.');
                            return;
                        }

                        revokeTemp();
                        tempUrl = URL.createObjectURL(file);
                        removeFlag.value = '0';
                        clearError();
                        setPreview(tempUrl);
                    });

                    setPreview(preview.getAttribute('src') || '');
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProfileUploaders);
            } else {
                initProfileUploaders();
            }
        })();
    </script>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/components/profile-image-uploader.blade.php ENDPATH**/ ?>