<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $rooms = $rooms ?? collect();
        $boardingHouses = $boardingHouses ?? collect();
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Room Management</h2>
            <button
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                id="openAddRoomModal"
                data-store-url="<?php echo e(route('admin.rooms.store')); ?>"
            >
                <span class="text-base">+</span>
                <span>Add Room</span>
            </button>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <p class="text-sm text-gray-600">Quick view of your current room inventory.</p>
            <div id="roomSuccess" class="mt-3 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm hidden"></div>
            <div id="roomError" class="mt-3 px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm hidden"></div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Boardinghouse</th>
                        <th class="px-5 py-3 text-left">Room No.</th>
                        <th class="px-5 py-3 text-left">Description</th>
                        <th class="px-5 py-3 text-left">Room Image</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="roomTableBody" class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $imageUrl = $room->image ? \Illuminate\Support\Facades\Storage::url($room->image) : '';
                            $houseName = $room->boardingHouse?->name ?? '';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-900" data-room-col="house"><?php echo e($houseName); ?></td>
                            <td class="px-5 py-3 text-gray-700" data-room-col="no"><?php echo e($room->room_no); ?></td>
                            <td class="px-5 py-3 text-gray-700" data-room-col="description"><?php echo e($room->description); ?></td>
                            <td class="px-5 py-3" data-room-col="image">
                                <img
                                    src="<?php echo e($imageUrl); ?>"
                                    alt="Room image"
                                    class="h-16 w-24 object-cover rounded-md border border-gray-100"
                                    data-room-image
                                    data-fallback="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=320&q=80"
                                    onerror="this.src=this.dataset.fallback;"
                                >
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold text-gray-700 hover:bg-gray-50 edit-room"
                                        data-room-id="<?php echo e($room->id); ?>"
                                        data-room-no="<?php echo e($room->room_no); ?>"
                                        data-description="<?php echo e($room->description); ?>"
                                        data-house-id="<?php echo e($room->boarding_house_id); ?>"
                                        data-house-name="<?php echo e($houseName); ?>"
                                        data-image="<?php echo e($imageUrl); ?>"
                                        data-update-url="<?php echo e(route('admin.rooms.update', $room)); ?>"
                                        data-delete-url="<?php echo e(route('admin.rooms.destroy', $room)); ?>"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 delete-room"
                                        data-room-id="<?php echo e($room->id); ?>"
                                        data-delete-url="<?php echo e(route('admin.rooms.destroy', $room)); ?>"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-gray-500">No rooms yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="roomModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl mx-4 flex flex-col" style="width: min(720px, 92vw); max-height: 85vh;">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Edit Room</h3>
                <button id="closeRoomModal" class="text-gray-400 hover:text-gray-600 text-xl" aria-label="Close">x</button>
            </div>
            <div id="roomModalBody" class="px-6 py-6 space-y-4 text-sm overflow-y-auto flex-1">
                <div id="roomModalError" class="px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm hidden"></div>
                <div id="roomModalSuccess" class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm hidden"></div>

                <label class="text-xs text-gray-500">
                    Boardinghouse Name
                    <select id="editRoomHouse" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800">
                        <option value="">Select boarding house</option>
                        <?php $__currentLoopData = $boardingHouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($house->id); ?>"><?php echo e($house->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="text-xs text-gray-500">
                    Room No.
                    <input id="editRoomNo" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800">
                </label>

                <label class="text-xs text-gray-500">
                    Room Description
                    <textarea id="editRoomDescription" rows="3" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800"></textarea>
                </label>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500">Room Image</p>
                        <div class="flex items-center gap-2">
                            <button id="editRoomImageBtn" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                            <button id="removeRoomImageBtn" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                            <button id="cancelRoomImageBtn" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                        </div>
                    </div>
                    <img id="editRoomImagePreview" class="w-full object-cover rounded-md border border-gray-100" style="height: 200px; width: 100%;" alt="Room preview" data-fallback="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=640&q=80">
                    <p id="editRoomImageError" class="text-xs text-rose-600 hidden"></p>
                </div>

                <input id="editRoomImageFile" type="file" accept="image/*" class="hidden">
            </div>
            <div class="px-6 py-5 border-t border-gray-100 flex justify-end gap-2 flex-shrink-0">
                <button id="saveRoomChanges" type="button" class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-base font-semibold">Save</button>
                <button id="closeRoomModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
            </div>
        </div>
    </div>

    <div id="addRoomModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl mx-4 flex flex-col" style="width: min(720px, 92vw); max-height: 85vh;">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Add Room</h3>
                <button id="closeAddRoomModal" class="text-gray-400 hover:text-gray-600 text-xl" aria-label="Close">x</button>
            </div>
            <div id="addRoomModalBody" class="px-6 py-6 space-y-4 text-sm overflow-y-auto flex-1">
                <div id="addRoomModalError" class="px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm hidden"></div>
                <div id="addRoomModalSuccess" class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm hidden"></div>

                <label class="text-xs text-gray-500">
                    Boardinghouse Name
                    <select id="addRoomHouse" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800">
                        <option value="">Select boarding house</option>
                        <?php $__currentLoopData = $boardingHouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($house->id); ?>"><?php echo e($house->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="text-xs text-gray-500">
                    Room No.
                    <input id="addRoomNo" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800">
                </label>

                <label class="text-xs text-gray-500">
                    Room Description
                    <textarea id="addRoomDescription" rows="3" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800"></textarea>
                </label>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500">Room Image</p>
                        <div class="flex items-center gap-2">
                            <button id="addRoomImageBtn" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                            <button id="removeAddRoomImageBtn" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                            <button id="cancelAddRoomImageBtn" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                        </div>
                    </div>
                    <img id="addRoomImagePreview" class="w-full object-cover rounded-md border border-gray-100" style="height: 200px; width: 100%;" alt="Room preview" data-fallback="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=640&q=80">
                    <p id="addRoomImageError" class="text-xs text-rose-600 hidden"></p>
                </div>

                <input id="addRoomImageFile" type="file" accept="image/*" class="hidden">
            </div>
            <div class="px-6 py-5 border-t border-gray-100 flex justify-end gap-2 flex-shrink-0">
                <button id="saveAddRoom" type="button" class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-base font-semibold">Save</button>
                <button id="closeAddRoomModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roomTableBody = document.getElementById('roomTableBody');
            const roomSuccess = document.getElementById('roomSuccess');
            const roomError = document.getElementById('roomError');

            const openAddRoomModal = document.getElementById('openAddRoomModal');
            const addRoomModal = document.getElementById('addRoomModal');
            const addRoomModalBody = document.getElementById('addRoomModalBody');
            const closeAddRoomModalButtons = [document.getElementById('closeAddRoomModal'), document.getElementById('closeAddRoomModalFooter')];
            const saveAddRoom = document.getElementById('saveAddRoom');
            const addRoomModalError = document.getElementById('addRoomModalError');
            const addRoomModalSuccess = document.getElementById('addRoomModalSuccess');

            const addRoomHouse = document.getElementById('addRoomHouse');
            const addRoomNo = document.getElementById('addRoomNo');
            const addRoomDescription = document.getElementById('addRoomDescription');

            const addRoomImageFile = document.getElementById('addRoomImageFile');
            const addRoomImagePreview = document.getElementById('addRoomImagePreview');
            const addRoomImageBtn = document.getElementById('addRoomImageBtn');
            const removeAddRoomImageBtn = document.getElementById('removeAddRoomImageBtn');
            const cancelAddRoomImageBtn = document.getElementById('cancelAddRoomImageBtn');
            const addRoomImageError = document.getElementById('addRoomImageError');

            const roomModal = document.getElementById('roomModal');
            const roomModalBody = document.getElementById('roomModalBody');
            const closeRoomModalButtons = [document.getElementById('closeRoomModal'), document.getElementById('closeRoomModalFooter')];
            const saveRoomChanges = document.getElementById('saveRoomChanges');
            const roomModalError = document.getElementById('roomModalError');
            const roomModalSuccess = document.getElementById('roomModalSuccess');

            const editRoomHouse = document.getElementById('editRoomHouse');
            const editRoomNo = document.getElementById('editRoomNo');
            const editRoomDescription = document.getElementById('editRoomDescription');

            const editRoomImageFile = document.getElementById('editRoomImageFile');
            const editRoomImagePreview = document.getElementById('editRoomImagePreview');
            const editRoomImageBtn = document.getElementById('editRoomImageBtn');
            const removeRoomImageBtn = document.getElementById('removeRoomImageBtn');
            const cancelRoomImageBtn = document.getElementById('cancelRoomImageBtn');
            const editRoomImageError = document.getElementById('editRoomImageError');

            let activeRow = null;
            let activeEditBtn = null;

            const imageState = {
                tempUrl: '',
                removed: false,
                original: '',
            };

            const addImageState = {
                tempUrl: '',
                removed: false,
                original: '',
            };

            const showElement = (el) => {
                if (el) el.classList.remove('hidden');
            };

            const hideElement = (el) => {
                if (el) el.classList.add('hidden');
            };

            const showMessage = (el, message) => {
                if (!el) return;
                el.textContent = message;
                showElement(el);
            };

            const clearMessage = (el) => {
                if (!el) return;
                el.textContent = '';
                hideElement(el);
            };

            const openOverlay = (el) => {
                if (!el) return;
                el.classList.remove('hidden');
                el.classList.add('flex');
            };

            const closeOverlay = (el) => {
                if (!el) return;
                el.classList.add('hidden');
                el.classList.remove('flex');
            };

            const isValidImageUrl = (value) => {
                if (!value) return false;
                if (value.startsWith('/')) return true;
                try {
                    const url = new URL(value, window.location.origin);
                    return ['http:', 'https:', 'blob:'].includes(url.protocol);
                } catch (error) {
                    return false;
                }
            };

            const setImage = (element, value) => {
                if (!element) return;
                const fallback = element.dataset.fallback || '';
                const nextSrc = isValidImageUrl(value) ? value : fallback;
                if (nextSrc) {
                    element.src = nextSrc;
                } else {
                    element.removeAttribute('src');
                }
            };

            const validateFile = (file, errorEl) => {
                if (!file) return false;
                const maxBytes = 5 * 1024 * 1024;
                const allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowed.includes(file.type)) {
                    if (errorEl) {
                        errorEl.textContent = 'Only JPG, PNG, or WEBP files are allowed.';
                        showElement(errorEl);
                    }
                    return false;
                }
                if (file.size > maxBytes) {
                    if (errorEl) {
                        errorEl.textContent = 'File size must be 5MB or less.';
                        showElement(errorEl);
                    }
                    return false;
                }
                if (errorEl) hideElement(errorEl);
                return true;
            };

            const resetImageState = (imageUrl) => {
                if (imageState.tempUrl) {
                    URL.revokeObjectURL(imageState.tempUrl);
                }
                imageState.tempUrl = '';
                imageState.removed = false;
                imageState.original = imageUrl || '';
                if (editRoomImageFile) editRoomImageFile.value = '';
                hideElement(cancelRoomImageBtn);
                hideElement(editRoomImageError);
                setImage(editRoomImagePreview, imageState.original);
            };

            const resetAddRoomImageState = () => {
                if (addImageState.tempUrl) {
                    URL.revokeObjectURL(addImageState.tempUrl);
                }
                addImageState.tempUrl = '';
                addImageState.removed = false;
                addImageState.original = addRoomImagePreview?.dataset.fallback || '';
                if (addRoomImageFile) addRoomImageFile.value = '';
                hideElement(cancelAddRoomImageBtn);
                hideElement(addRoomImageError);
                setImage(addRoomImagePreview, addImageState.original);
            };

            const resetAddRoomForm = () => {
                if (addRoomHouse) addRoomHouse.value = '';
                if (addRoomNo) addRoomNo.value = '';
                if (addRoomDescription) addRoomDescription.value = '';
                clearMessage(addRoomModalError);
                clearMessage(addRoomModalSuccess);
                resetAddRoomImageState();
            };

            const createRoomRow = (room) => {
                if (!roomTableBody) return;
                const emptyCell = roomTableBody.querySelector('td[colspan="5"]');
                if (emptyCell) {
                    emptyCell.closest('tr')?.remove();
                }

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const houseCell = document.createElement('td');
                houseCell.className = 'px-5 py-3 text-gray-900';
                houseCell.dataset.roomCol = 'house';
                houseCell.textContent = room.boarding_house_name || '';

                const noCell = document.createElement('td');
                noCell.className = 'px-5 py-3 text-gray-700';
                noCell.dataset.roomCol = 'no';
                noCell.textContent = room.room_no || '';

                const descCell = document.createElement('td');
                descCell.className = 'px-5 py-3 text-gray-700';
                descCell.dataset.roomCol = 'description';
                descCell.textContent = room.description || '';

                const imageCell = document.createElement('td');
                imageCell.className = 'px-5 py-3';
                imageCell.dataset.roomCol = 'image';
                const img = document.createElement('img');
                img.className = 'h-16 w-24 object-cover rounded-md border border-gray-100';
                img.alt = 'Room image';
                img.dataset.roomImage = '';
                img.dataset.fallback = 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=320&q=80';
                img.onerror = function () {
                    this.src = this.dataset.fallback;
                };
                setImage(img, room.image_url || '');
                imageCell.appendChild(img);

                const actionCell = document.createElement('td');
                actionCell.className = 'px-5 py-3 text-right';
                const actions = document.createElement('div');
                actions.className = 'flex justify-end items-center gap-2';

                const editBtn = document.createElement('button');
                editBtn.type = 'button';
                editBtn.className = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold text-gray-700 hover:bg-gray-50 edit-room';
                editBtn.dataset.roomId = room.id;
                editBtn.dataset.roomNo = room.room_no || '';
                editBtn.dataset.description = room.description || '';
                editBtn.dataset.houseId = room.boarding_house_id || '';
                editBtn.dataset.houseName = room.boarding_house_name || '';
                editBtn.dataset.image = room.image_url || '';
                editBtn.dataset.updateUrl = room.update_url || '';
                editBtn.dataset.deleteUrl = room.delete_url || '';
                editBtn.textContent = 'Edit';

                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 delete-room';
                deleteBtn.dataset.roomId = room.id;
                deleteBtn.dataset.deleteUrl = room.delete_url || '';
                deleteBtn.textContent = 'Delete';

                actions.appendChild(editBtn);
                actions.appendChild(deleteBtn);
                actionCell.appendChild(actions);

                row.appendChild(houseCell);
                row.appendChild(noCell);
                row.appendChild(descCell);
                row.appendChild(imageCell);
                row.appendChild(actionCell);

                roomTableBody.prepend(row);
            };

            const openRoomModal = (btn) => {
                activeEditBtn = btn;
                activeRow = btn.closest('tr');

                if (editRoomHouse) editRoomHouse.value = btn.dataset.houseId || '';
                if (editRoomNo) editRoomNo.value = btn.dataset.roomNo || '';
                if (editRoomDescription) editRoomDescription.value = btn.dataset.description || '';

                resetImageState(btn.dataset.image || '');

                clearMessage(roomModalError);
                clearMessage(roomModalSuccess);

                openOverlay(roomModal);
            };

            const openAddModal = () => {
                resetAddRoomForm();
                openOverlay(addRoomModal);
                addRoomHouse?.focus();
            };

            const closeAddModal = () => {
                closeOverlay(addRoomModal);
            };

            const closeRoomModal = () => {
                closeOverlay(roomModal);
            };

            if (roomTableBody) {
                roomTableBody.addEventListener('click', (event) => {
                    const editBtn = event.target.closest('.edit-room');
                    if (editBtn) {
                        openRoomModal(editBtn);
                        return;
                    }

                    const deleteBtn = event.target.closest('.delete-room');
                    if (deleteBtn) {
                        const deleteUrl = deleteBtn.dataset.deleteUrl || '';
                        if (!deleteUrl) {
                            showMessage(roomError, 'Unable to delete: missing route.');
                            return;
                        }

                        if (!confirm('Delete this room?')) return;

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const formData = new FormData();
                        if (csrfToken) formData.append('_token', csrfToken);
                        formData.append('_method', 'DELETE');

                        fetch(deleteUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: formData,
                        })
                            .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
                            .then(({ ok, data }) => {
                                if (!ok) {
                                    showMessage(roomError, data?.message || 'Unable to delete room.');
                                    return;
                                }
                                deleteBtn.closest('tr')?.remove();
                                showMessage(roomSuccess, data?.message || 'Room deleted.');
                                setTimeout(() => hideElement(roomSuccess), 3000);
                            })
                            .catch(() => showMessage(roomError, 'Unable to delete room.'));
                    }
                });
            }

            if (editRoomImageBtn && editRoomImageFile) {
                editRoomImageBtn.addEventListener('click', () => editRoomImageFile.click());
                editRoomImageFile.addEventListener('change', () => {
                    const file = editRoomImageFile.files?.[0];
                    if (!validateFile(file, editRoomImageError)) {
                        if (editRoomImageFile) editRoomImageFile.value = '';
                        return;
                    }
                    if (imageState.tempUrl) URL.revokeObjectURL(imageState.tempUrl);
                    imageState.tempUrl = URL.createObjectURL(file);
                    imageState.removed = false;
                    setImage(editRoomImagePreview, imageState.tempUrl);
                    showElement(cancelRoomImageBtn);
                });
            }

            if (removeRoomImageBtn) {
                removeRoomImageBtn.addEventListener('click', () => {
                    if (imageState.tempUrl) URL.revokeObjectURL(imageState.tempUrl);
                    imageState.tempUrl = '';
                    imageState.removed = true;
                    if (editRoomImageFile) editRoomImageFile.value = '';
                    hideElement(editRoomImageError);
                    setImage(editRoomImagePreview, '');
                    showElement(cancelRoomImageBtn);
                });
            }

            if (cancelRoomImageBtn) {
                cancelRoomImageBtn.addEventListener('click', () => {
                    resetImageState(imageState.original);
                });
            }

            if (saveRoomChanges) {
                saveRoomChanges.addEventListener('click', async () => {
                    clearMessage(roomModalError);
                    clearMessage(roomModalSuccess);

                    const updateUrl = activeEditBtn?.dataset.updateUrl || '';
                    if (!updateUrl) {
                        showMessage(roomModalError, 'Unable to save: missing update route.');
                        return;
                    }

                    const boardingHouseId = editRoomHouse?.value || '';
                    const roomNo = editRoomNo?.value.trim() || '';
                    const description = editRoomDescription?.value.trim() || '';

                    const missingFields = [];
                    if (!boardingHouseId) missingFields.push('Boardinghouse Name');
                    if (!roomNo) missingFields.push('Room No.');

                    if (missingFields.length) {
                        showMessage(roomModalError, `Please fill in: ${missingFields.join(', ')}.`);
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const formData = new FormData();
                    if (csrfToken) formData.append('_token', csrfToken);
                    formData.append('_method', 'PUT');
                    formData.append('boarding_house_id', boardingHouseId);
                    formData.append('room_no', roomNo);
                    formData.append('description', description);

                    if (imageState.removed) {
                        formData.append('remove_image', '1');
                    }

                    const imageFile = editRoomImageFile?.files?.[0];
                    if (imageFile) {
                        formData.append('image', imageFile);
                    }

                    saveRoomChanges.disabled = true;
                    saveRoomChanges.classList.add('opacity-70', 'cursor-not-allowed');

                    try {
                        const response = await fetch(updateUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: formData,
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (response.status === 422 && data?.errors) {
                                const errorText = Object.values(data.errors).flat().join(' ');
                                showMessage(roomModalError, errorText || 'Please check the highlighted fields.');
                            } else {
                                showMessage(roomModalError, data?.message || 'Unable to save changes.');
                            }
                            return;
                        }

                        const room = data?.room || {};
                        const houseName = room.boarding_house_name || editRoomHouse?.selectedOptions?.[0]?.textContent || '';
                        const imageUrl = room.image_url ?? activeEditBtn?.dataset.image ?? '';

                        if (activeRow) {
                            const houseCell = activeRow.querySelector('[data-room-col="house"]');
                            const noCell = activeRow.querySelector('[data-room-col="no"]');
                            const descCell = activeRow.querySelector('[data-room-col="description"]');
                            const imgEl = activeRow.querySelector('[data-room-image]');

                            if (houseCell) houseCell.textContent = houseName;
                            if (noCell) noCell.textContent = room.room_no ?? roomNo;
                            if (descCell) descCell.textContent = room.description ?? description;
                            if (imgEl) setImage(imgEl, imageUrl);
                        }

                        if (activeEditBtn) {
                            activeEditBtn.dataset.houseId = room.boarding_house_id || boardingHouseId;
                            activeEditBtn.dataset.houseName = houseName;
                            activeEditBtn.dataset.roomNo = room.room_no ?? roomNo;
                            activeEditBtn.dataset.description = room.description ?? description;
                            activeEditBtn.dataset.image = imageUrl;
                            if (room.update_url) activeEditBtn.dataset.updateUrl = room.update_url;
                            if (room.delete_url) activeEditBtn.dataset.deleteUrl = room.delete_url;
                        }

                        resetImageState(imageUrl);
                        showMessage(roomModalSuccess, 'Successfully saved.');
                        if (roomModalBody) {
                            roomModalBody.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    } catch (error) {
                        showMessage(roomModalError, 'Something went wrong. Please try again.');
                    } finally {
                        saveRoomChanges.disabled = false;
                        saveRoomChanges.classList.remove('opacity-70', 'cursor-not-allowed');
                    }
                });
            }

            if (addRoomImageBtn && addRoomImageFile) {
                addRoomImageBtn.addEventListener('click', () => addRoomImageFile.click());
                addRoomImageFile.addEventListener('change', () => {
                    const file = addRoomImageFile.files?.[0];
                    if (!validateFile(file, addRoomImageError)) {
                        if (addRoomImageFile) addRoomImageFile.value = '';
                        return;
                    }
                    if (addImageState.tempUrl) URL.revokeObjectURL(addImageState.tempUrl);
                    addImageState.tempUrl = URL.createObjectURL(file);
                    addImageState.removed = false;
                    setImage(addRoomImagePreview, addImageState.tempUrl);
                    showElement(cancelAddRoomImageBtn);
                });
            }

            if (removeAddRoomImageBtn) {
                removeAddRoomImageBtn.addEventListener('click', () => {
                    if (addImageState.tempUrl) URL.revokeObjectURL(addImageState.tempUrl);
                    addImageState.tempUrl = '';
                    addImageState.removed = true;
                    if (addRoomImageFile) addRoomImageFile.value = '';
                    hideElement(addRoomImageError);
                    setImage(addRoomImagePreview, '');
                    showElement(cancelAddRoomImageBtn);
                });
            }

            if (cancelAddRoomImageBtn) {
                cancelAddRoomImageBtn.addEventListener('click', () => {
                    resetAddRoomImageState();
                });
            }

            if (openAddRoomModal) {
                openAddRoomModal.addEventListener('click', openAddModal);
            }

            closeAddRoomModalButtons.forEach((btn) => btn?.addEventListener('click', closeAddModal));
            if (addRoomModal) {
                addRoomModal.addEventListener('click', (e) => {
                    if (e.target === addRoomModal) closeAddModal();
                });
            }

            if (saveAddRoom) {
                saveAddRoom.addEventListener('click', async () => {
                    clearMessage(addRoomModalError);
                    clearMessage(addRoomModalSuccess);

                    const storeUrl = openAddRoomModal?.dataset.storeUrl || '';
                    if (!storeUrl) {
                        showMessage(addRoomModalError, 'Unable to save: missing route.');
                        return;
                    }

                    const boardingHouseId = addRoomHouse?.value || '';
                    const roomNo = addRoomNo?.value.trim() || '';
                    const description = addRoomDescription?.value.trim() || '';

                    const missingFields = [];
                    if (!boardingHouseId) missingFields.push('Boardinghouse Name');
                    if (!roomNo) missingFields.push('Room No.');

                    if (missingFields.length) {
                        showMessage(addRoomModalError, `Please fill in: ${missingFields.join(', ')}.`);
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const formData = new FormData();
                    if (csrfToken) formData.append('_token', csrfToken);
                    formData.append('boarding_house_id', boardingHouseId);
                    formData.append('room_no', roomNo);
                    formData.append('description', description);

                    const imageFile = addRoomImageFile?.files?.[0];
                    if (imageFile && !addImageState.removed) {
                        formData.append('image', imageFile);
                    }

                    saveAddRoom.disabled = true;
                    saveAddRoom.classList.add('opacity-70', 'cursor-not-allowed');

                    try {
                        const response = await fetch(storeUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: formData,
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (response.status === 422 && data?.errors) {
                                const errorText = Object.values(data.errors).flat().join(' ');
                                showMessage(addRoomModalError, errorText || 'Please check the highlighted fields.');
                            } else {
                                showMessage(addRoomModalError, data?.message || 'Unable to save room.');
                            }
                            return;
                        }

                        if (data?.room) {
                            createRoomRow(data.room);
                        }

                        closeAddModal();
                        showMessage(roomSuccess, data?.message || 'Room saved.');
                        setTimeout(() => hideElement(roomSuccess), 3000);
                    } catch (error) {
                        showMessage(addRoomModalError, 'Something went wrong. Please try again.');
                    } finally {
                        saveAddRoom.disabled = false;
                        saveAddRoom.classList.remove('opacity-70', 'cursor-not-allowed');
                    }
                });
            }

            closeRoomModalButtons.forEach((btn) => btn?.addEventListener('click', closeRoomModal));
            if (roomModal) {
                roomModal.addEventListener('click', (e) => {
                    if (e.target === roomModal) closeRoomModal();
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                if (addRoomModal && !addRoomModal.classList.contains('hidden')) {
                    closeAddModal();
                    return;
                }
                if (roomModal && !roomModal.classList.contains('hidden')) {
                    closeRoomModal();
                }
            });
        });
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/owner/rooms.blade.php ENDPATH**/ ?>