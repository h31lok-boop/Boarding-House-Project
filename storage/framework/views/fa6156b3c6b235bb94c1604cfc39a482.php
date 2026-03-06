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
        $houses = $houses ?? collect();
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boarding Houses</h2>
            <button
                id="openAddHouseModal"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                data-store-url="<?php echo e(route('admin.boarding-houses.store')); ?>"
            >
                <span class="text-base">+</span>
                <span>Add Boarding House</span>
            </button>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <p class="text-sm text-gray-600">Track your properties with landlord details and quick actions.</p>
            <div id="addHouseSuccess" class="mt-3 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm hidden"></div>
            <div id="addHouseError" class="mt-3 px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm hidden"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Boarding House</th>
                        <th class="px-5 py-3 text-left">Landlord</th>
                        <th class="px-5 py-3 text-left">Address</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="boardingHouseTableBody" class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $exteriorUrl = $house->exterior_image ? \Illuminate\Support\Facades\Storage::url($house->exterior_image) : '';
                            $roomUrl = $house->room_image ? \Illuminate\Support\Facades\Storage::url($house->room_image) : '';
                            $crUrl = $house->cr_image ? \Illuminate\Support\Facades\Storage::url($house->cr_image) : '';
                            $kitchenUrl = $house->kitchen_image ? \Illuminate\Support\Facades\Storage::url($house->kitchen_image) : '';
                            $statusLabel = $house->is_active ? 'Active' : 'Inactive';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-900"><?php echo e($house->name); ?></td>
                            <td class="px-5 py-3 text-gray-700"><?php echo e($house->landlord_info ?? ''); ?></td>
                            <td class="px-5 py-3 text-gray-700"><?php echo e($house->address ?? ''); ?></td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                        data-name="<?php echo e($house->name); ?>"
                                        data-landlord="<?php echo e($house->landlord_info ?? ''); ?>"
                                        data-address="<?php echo e($house->address ?? ''); ?>"
                                        data-status="<?php echo e($statusLabel); ?>"
                                        data-image="<?php echo e($exteriorUrl); ?>"
                                        data-room="<?php echo e($roomUrl); ?>"
                                        data-cr="<?php echo e($crUrl); ?>"
                                        data-kitchen="<?php echo e($kitchenUrl); ?>"
                                        data-monthly="<?php echo e($house->monthly_payment ?? ''); ?>"
                                        data-update-url="<?php echo e(route('admin.boarding-houses.update', $house)); ?>"
                                        data-mode="view"
                                    >
                                        <span>View</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700"
                                        data-name="<?php echo e($house->name); ?>"
                                        data-landlord="<?php echo e($house->landlord_info ?? ''); ?>"
                                        data-address="<?php echo e($house->address ?? ''); ?>"
                                        data-status="<?php echo e($statusLabel); ?>"
                                        data-image="<?php echo e($exteriorUrl); ?>"
                                        data-room="<?php echo e($roomUrl); ?>"
                                        data-cr="<?php echo e($crUrl); ?>"
                                        data-kitchen="<?php echo e($kitchenUrl); ?>"
                                        data-monthly="<?php echo e($house->monthly_payment ?? ''); ?>"
                                        data-update-url="<?php echo e(route('admin.boarding-houses.update', $house)); ?>"
                                        data-mode="manage"
                                    >
                                        <span>Manage</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-gray-500">No boarding houses yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addHouseModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl mx-4 flex flex-col" style="width: min(720px, 92vw); max-height: 85vh;">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Add Boarding House</h3>
                <button id="closeAddHouseModal" class="text-gray-400 hover:text-gray-600 text-xl" aria-label="Close">x</button>
            </div>
            <div id="addHouseModalBody" class="px-6 py-6 space-y-4 text-sm overflow-y-auto flex-1">
                <div id="addHouseModalError" class="px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm hidden"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="text-xs text-gray-500">
                        Name
                        <input id="addHouseName" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800" required>
                    </label>
                    <label class="text-xs text-gray-500">
                        Monthly Payment
                        <input id="addHouseMonthly" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800" required>
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="text-xs text-gray-500">
                        Landlord
                        <input id="addHouseLandlord" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800" required>
                    </label>
                    <label class="text-xs text-gray-500">
                        Address
                        <input id="addHouseAddress" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800" required>
                    </label>
                </div>
                <label class="text-xs text-gray-500">
                    Description
                    <textarea id="addHouseDescription" rows="3" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800" required></textarea>
                </label>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500">Room</p>
                        <div class="flex items-center gap-2">
                            <button id="addRoomEdit" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                            <button id="addRoomRemove" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                            <button id="addRoomCancel" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                        </div>
                    </div>
                    <img id="addRoomPreview" class="w-full object-cover rounded-md border border-gray-100" style="height: 200px; width: 100%;" alt="Room preview" data-fallback="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=640&q=80">
                    <p id="addRoomError" class="text-xs text-rose-600 hidden"></p>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500">CR</p>
                        <div class="flex items-center gap-2">
                            <button id="addCrEdit" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                            <button id="addCrRemove" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                            <button id="addCrCancel" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                        </div>
                    </div>
                    <img id="addCrPreview" class="w-full object-cover rounded-md border border-gray-100" style="height: 200px; width: 100%;" alt="CR preview" data-fallback="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?auto=format&fit=crop&w=640&q=80">
                    <p id="addCrError" class="text-xs text-rose-600 hidden"></p>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500">Kitchen</p>
                        <div class="flex items-center gap-2">
                            <button id="addKitchenEdit" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                            <button id="addKitchenRemove" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                            <button id="addKitchenCancel" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                        </div>
                    </div>
                    <img id="addKitchenPreview" class="w-full object-cover rounded-md border border-gray-100" style="height: 200px; width: 100%;" alt="Kitchen preview" data-fallback="https://images.unsplash.com/photo-1507089947368-19c1da9775ae?auto=format&fit=crop&w=640&q=80">
                    <p id="addKitchenError" class="text-xs text-rose-600 hidden"></p>
                </div>
                <input id="addRoomFile" type="file" accept="image/*" class="hidden">
                <input id="addCrFile" type="file" accept="image/*" class="hidden">
                <input id="addKitchenFile" type="file" accept="image/*" class="hidden">
            </div>
            <div class="px-6 py-5 border-t border-gray-100 flex justify-end gap-2 flex-shrink-0">
                <button id="saveAddHouse" type="button" class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-base font-semibold">Save</button>
                <button id="closeAddHouseModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
            </div>
        </div>
    </div>
    <div id="houseModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl mx-4 flex flex-col" style="width: min(720px, 92vw); max-height: 85vh;">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Boarding House Details</h3>
                <button id="closeHouseModal" class="text-gray-400 hover:text-gray-600 text-xl" aria-label="Close">x</button>
            </div>
            <div id="houseModalBody" class="px-6 py-6 space-y-4 text-sm overflow-y-auto flex-1">
                <div id="manageSaveError" class="px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm hidden"></div>
                <div id="manageSaveSuccess" class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm hidden"></div>
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-gray-500">Exterior</p>
                    <div class="flex items-center gap-2 image-actions hidden">
                        <button id="editHouseImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                        <button id="removeHouseImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                        <button id="cancelHouseImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                    </div>
                </div>
                <div class="w-full rounded-md overflow-hidden border border-gray-100">
                    <img id="modalHouseHero"
                         class="w-full object-cover rounded-md"
                         style="height: 200px; width: 100%;"
                         alt="Boarding house exterior photo"
                         loading="lazy"
                         referrerpolicy="no-referrer"
                         data-fallback="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80"
                         onerror="this.src=this.dataset.fallback;">
                </div>
                <p id="houseImageError" class="text-xs text-rose-600 hidden"></p>
                <div class="space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500">Room</p>
                            <div class="flex items-center gap-2 image-actions hidden">
                                <button id="editRoomImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                                <button id="removeRoomImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                                <button id="cancelRoomImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                            </div>
                        </div>
                        <img id="modalRoomImage"
                             class="w-full object-cover rounded-md border border-gray-100"
                             style="height: 200px; width: 100%;"
                             alt="Room photo"
                             loading="lazy"
                             referrerpolicy="no-referrer"
                             data-fallback="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=640&q=80"
                             onerror="this.src=this.dataset.fallback;">
                        <p id="roomImageError" class="text-xs text-rose-600 hidden"></p>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500">CR</p>
                            <div class="flex items-center gap-2 image-actions hidden">
                                <button id="editCrImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                                <button id="removeCrImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                                <button id="cancelCrImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                            </div>
                        </div>
                        <img id="modalCrImage"
                             class="w-full object-cover rounded-md border border-gray-100"
                             style="height: 200px; width: 100%;"
                             alt="CR photo"
                             loading="lazy"
                             referrerpolicy="no-referrer"
                             data-fallback="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?auto=format&fit=crop&w=640&q=80"
                             onerror="this.src=this.dataset.fallback;">
                        <p id="crImageError" class="text-xs text-rose-600 hidden"></p>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500">Kitchen</p>
                            <div class="flex items-center gap-2 image-actions hidden">
                                <button id="editKitchenImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Edit</button>
                                <button id="removeKitchenImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">Remove</button>
                                <button id="cancelKitchenImage" type="button" class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 hidden">Cancel</button>
                            </div>
                        </div>
                        <img id="modalKitchenImage"
                             class="w-full object-cover rounded-md border border-gray-100"
                             style="height: 200px; width: 100%;"
                             alt="Kitchen photo"
                             loading="lazy"
                             referrerpolicy="no-referrer"
                             data-fallback="https://images.unsplash.com/photo-1507089947368-19c1da9775ae?auto=format&fit=crop&w=640&q=80"
                             onerror="this.src=this.dataset.fallback;">
                        <p id="kitchenImageError" class="text-xs text-rose-600 hidden"></p>
                    </div>
                </div>
                <div id="viewDetails" class="space-y-4">
                    <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                        <span class="text-gray-500 font-medium">Name</span>
                        <span id="modalHouseName" class="font-semibold text-gray-900"></span>
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                        <span class="text-gray-500 font-medium">Landlord</span>
                        <span id="modalHouseLandlord" class="text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                        <span class="text-gray-500 font-medium">Address</span>
                        <span id="modalHouseAddress" class="text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                        <span class="text-gray-500 font-medium">Monthly Payment</span>
                        <span id="modalHouseMonthly" class="text-gray-800 font-semibold"></span>
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-6">
                        <span class="text-gray-500 font-medium">Status</span>
                        <span id="modalHouseStatus" class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold bg-gray-50 text-gray-700 border-gray-100"></span>
                    </div>
                </div>

                <div id="manageSection" class="pt-4 border-t border-gray-100 space-y-3 hidden">
                    <h4 class="text-sm font-semibold text-gray-700">Edit Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="text-xs text-gray-500">
                            Name
                            <input id="editHouseName" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </label>
                        <label class="text-xs text-gray-500">
                            Landlord
                            <input id="editHouseLandlord" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </label>
                    </div>
                    <label class="text-xs text-gray-500">
                        Address
                        <input id="editHouseAddress" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="text-xs text-gray-500">
                            Monthly Payment
                            <input id="editHouseMonthly" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </label>
                        <label class="text-xs text-gray-500">
                            Status
                            <input id="editHouseStatus" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </label>
                    </div>
                    <input id="editHouseImageFile" type="file" accept="image/*" class="hidden">
                    <input id="editRoomImageFile" type="file" accept="image/*" class="hidden">
                    <input id="editCrImageFile" type="file" accept="image/*" class="hidden">
                    <input id="editKitchenImageFile" type="file" accept="image/*" class="hidden">
                </div>
            </div>
            <div class="px-6 py-5 border-t border-gray-100 flex justify-end gap-2 flex-shrink-0">
                <button id="saveHouseChanges" type="button" class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-base font-semibold hidden">Save</button>
                <button id="closeHouseModalFooter" class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-base font-semibold">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('houseModal');
            const closeButtons = [document.getElementById('closeHouseModal'), document.getElementById('closeHouseModalFooter')];
            const manageSection = document.getElementById('manageSection');
            const viewDetails = document.getElementById('viewDetails');
            const saveButton = document.getElementById('saveHouseChanges');
            const tableBody = document.getElementById('boardingHouseTableBody');
            const imageActions = document.querySelectorAll('.image-actions');

            const editName = document.getElementById('editHouseName');
            const editLandlord = document.getElementById('editHouseLandlord');
            const editAddress = document.getElementById('editHouseAddress');
            const editMonthly = document.getElementById('editHouseMonthly');
            const editStatus = document.getElementById('editHouseStatus');

            const editHouseImageFile = document.getElementById('editHouseImageFile');
            const editRoomImageFile = document.getElementById('editRoomImageFile');
            const editCrImageFile = document.getElementById('editCrImageFile');
            const editKitchenImageFile = document.getElementById('editKitchenImageFile');

            const editHouseImage = document.getElementById('editHouseImage');
            const editRoomImage = document.getElementById('editRoomImage');
            const editCrImage = document.getElementById('editCrImage');
            const editKitchenImage = document.getElementById('editKitchenImage');

            const removeHouseImage = document.getElementById('removeHouseImage');
            const removeRoomImage = document.getElementById('removeRoomImage');
            const removeCrImage = document.getElementById('removeCrImage');
            const removeKitchenImage = document.getElementById('removeKitchenImage');

            const cancelHouseImage = document.getElementById('cancelHouseImage');
            const cancelRoomImage = document.getElementById('cancelRoomImage');
            const cancelCrImage = document.getElementById('cancelCrImage');
            const cancelKitchenImage = document.getElementById('cancelKitchenImage');

            const houseImageError = document.getElementById('houseImageError');
            const roomImageError = document.getElementById('roomImageError');
            const crImageError = document.getElementById('crImageError');
            const kitchenImageError = document.getElementById('kitchenImageError');
            const manageSaveError = document.getElementById('manageSaveError');
            const manageSaveSuccess = document.getElementById('manageSaveSuccess');

            const addModal = document.getElementById('addHouseModal');
            const openAddButton = document.getElementById('openAddHouseModal');
            const addModalCloseButtons = [document.getElementById('closeAddHouseModal'), document.getElementById('closeAddHouseModalFooter')];
            const saveAddHouseButton = document.getElementById('saveAddHouse');
            const addHouseModalError = document.getElementById('addHouseModalError');
            const addHouseSuccess = document.getElementById('addHouseSuccess');
            const addHouseError = document.getElementById('addHouseError');
            const addStoreUrl = openAddButton?.dataset.storeUrl || '';

            const addHouseName = document.getElementById('addHouseName');
            const addHouseMonthly = document.getElementById('addHouseMonthly');
            const addHouseLandlord = document.getElementById('addHouseLandlord');
            const addHouseAddress = document.getElementById('addHouseAddress');
            const addHouseDescription = document.getElementById('addHouseDescription');

            const addRoomPreview = document.getElementById('addRoomPreview');
            const addCrPreview = document.getElementById('addCrPreview');
            const addKitchenPreview = document.getElementById('addKitchenPreview');

            const addRoomFile = document.getElementById('addRoomFile');
            const addCrFile = document.getElementById('addCrFile');
            const addKitchenFile = document.getElementById('addKitchenFile');

            const addRoomEdit = document.getElementById('addRoomEdit');
            const addCrEdit = document.getElementById('addCrEdit');
            const addKitchenEdit = document.getElementById('addKitchenEdit');

            const addRoomRemove = document.getElementById('addRoomRemove');
            const addCrRemove = document.getElementById('addCrRemove');
            const addKitchenRemove = document.getElementById('addKitchenRemove');

            const addRoomCancel = document.getElementById('addRoomCancel');
            const addCrCancel = document.getElementById('addCrCancel');
            const addKitchenCancel = document.getElementById('addKitchenCancel');

            const addRoomError = document.getElementById('addRoomError');
            const addCrError = document.getElementById('addCrError');
            const addKitchenError = document.getElementById('addKitchenError');

            let activeButton = null;
            let activeRow = null;

            const imageState = {
                tempUrls: {},
                removed: {},
                original: {},
            };

            const addImageState = {
                tempUrls: {},
                removed: {},
                original: {},
            };

            const showElement = (el) => {
                if (el) el.classList.remove('hidden');
            };

            const hideElement = (el) => {
                if (el) el.classList.add('hidden');
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

            const setText = (el, value) => {
                if (el) el.textContent = value ?? '';
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

            const clearTempUrls = (state) => {
                if (!state) return;
                Object.values(state.tempUrls).forEach((url) => {
                    if (url) URL.revokeObjectURL(url);
                });
                state.tempUrls = {};
            };

            const setTempImage = (state, key, file, errorEl, previewEl, cancelBtn, fileInput) => {
                if (!validateFile(file, errorEl)) {
                    if (fileInput) fileInput.value = '';
                    return;
                }
                if (state.tempUrls[key]) {
                    URL.revokeObjectURL(state.tempUrls[key]);
                }
                const url = URL.createObjectURL(file);
                state.tempUrls[key] = url;
                state.removed[key] = false;
                setImage(previewEl, url);
                if (cancelBtn) cancelBtn.classList.remove('hidden');
            };

            const removeImage = (state, key, previewEl, errorEl, cancelBtn, fileInput) => {
                if (state.tempUrls[key]) {
                    URL.revokeObjectURL(state.tempUrls[key]);
                    delete state.tempUrls[key];
                }
                state.removed[key] = true;
                if (fileInput) fileInput.value = '';
                if (errorEl) hideElement(errorEl);
                setImage(previewEl, '');
                if (cancelBtn) cancelBtn.classList.remove('hidden');
            };

            const cancelImage = (state, key, previewEl, errorEl, cancelBtn, fileInput) => {
                if (state.tempUrls[key]) {
                    URL.revokeObjectURL(state.tempUrls[key]);
                    delete state.tempUrls[key];
                }
                state.removed[key] = false;
                if (fileInput) fileInput.value = '';
                if (errorEl) hideElement(errorEl);
                setImage(previewEl, state.original[key] || '');
                if (cancelBtn) cancelBtn.classList.add('hidden');
            };

            if (editHouseImage && editHouseImageFile) {
                editHouseImage.addEventListener('click', () => editHouseImageFile.click());
                editHouseImageFile.addEventListener('change', () => {
                    const file = editHouseImageFile.files?.[0];
                    setTempImage(imageState, 'image', file, houseImageError, document.getElementById('modalHouseHero'), cancelHouseImage, editHouseImageFile);
                });
            }
            if (editRoomImage && editRoomImageFile) {
                editRoomImage.addEventListener('click', () => editRoomImageFile.click());
                editRoomImageFile.addEventListener('change', () => {
                    const file = editRoomImageFile.files?.[0];
                    setTempImage(imageState, 'room', file, roomImageError, document.getElementById('modalRoomImage'), cancelRoomImage, editRoomImageFile);
                });
            }
            if (editCrImage && editCrImageFile) {
                editCrImage.addEventListener('click', () => editCrImageFile.click());
                editCrImageFile.addEventListener('change', () => {
                    const file = editCrImageFile.files?.[0];
                    setTempImage(imageState, 'cr', file, crImageError, document.getElementById('modalCrImage'), cancelCrImage, editCrImageFile);
                });
            }
            if (editKitchenImage && editKitchenImageFile) {
                editKitchenImage.addEventListener('click', () => editKitchenImageFile.click());
                editKitchenImageFile.addEventListener('change', () => {
                    const file = editKitchenImageFile.files?.[0];
                    setTempImage(imageState, 'kitchen', file, kitchenImageError, document.getElementById('modalKitchenImage'), cancelKitchenImage, editKitchenImageFile);
                });
            }

            if (removeHouseImage) {
                removeHouseImage.addEventListener('click', () => {
                    removeImage(imageState, 'image', document.getElementById('modalHouseHero'), houseImageError, cancelHouseImage, editHouseImageFile);
                });
            }
            if (removeRoomImage) {
                removeRoomImage.addEventListener('click', () => {
                    removeImage(imageState, 'room', document.getElementById('modalRoomImage'), roomImageError, cancelRoomImage, editRoomImageFile);
                });
            }
            if (removeCrImage) {
                removeCrImage.addEventListener('click', () => {
                    removeImage(imageState, 'cr', document.getElementById('modalCrImage'), crImageError, cancelCrImage, editCrImageFile);
                });
            }
            if (removeKitchenImage) {
                removeKitchenImage.addEventListener('click', () => {
                    removeImage(imageState, 'kitchen', document.getElementById('modalKitchenImage'), kitchenImageError, cancelKitchenImage, editKitchenImageFile);
                });
            }

            if (cancelHouseImage) {
                cancelHouseImage.addEventListener('click', () => {
                    cancelImage(imageState, 'image', document.getElementById('modalHouseHero'), houseImageError, cancelHouseImage, editHouseImageFile);
                });
            }
            if (cancelRoomImage) {
                cancelRoomImage.addEventListener('click', () => {
                    cancelImage(imageState, 'room', document.getElementById('modalRoomImage'), roomImageError, cancelRoomImage, editRoomImageFile);
                });
            }
            if (cancelCrImage) {
                cancelCrImage.addEventListener('click', () => {
                    cancelImage(imageState, 'cr', document.getElementById('modalCrImage'), crImageError, cancelCrImage, editCrImageFile);
                });
            }
            if (cancelKitchenImage) {
                cancelKitchenImage.addEventListener('click', () => {
                    cancelImage(imageState, 'kitchen', document.getElementById('modalKitchenImage'), kitchenImageError, cancelKitchenImage, editKitchenImageFile);
                });
            }

            if (addRoomEdit && addRoomFile) {
                addRoomEdit.addEventListener('click', () => addRoomFile.click());
                addRoomFile.addEventListener('change', () => {
                    const file = addRoomFile.files?.[0];
                    setTempImage(addImageState, 'room', file, addRoomError, addRoomPreview, addRoomCancel, addRoomFile);
                });
            }
            if (addCrEdit && addCrFile) {
                addCrEdit.addEventListener('click', () => addCrFile.click());
                addCrFile.addEventListener('change', () => {
                    const file = addCrFile.files?.[0];
                    setTempImage(addImageState, 'cr', file, addCrError, addCrPreview, addCrCancel, addCrFile);
                });
            }
            if (addKitchenEdit && addKitchenFile) {
                addKitchenEdit.addEventListener('click', () => addKitchenFile.click());
                addKitchenFile.addEventListener('change', () => {
                    const file = addKitchenFile.files?.[0];
                    setTempImage(addImageState, 'kitchen', file, addKitchenError, addKitchenPreview, addKitchenCancel, addKitchenFile);
                });
            }

            if (addRoomRemove) {
                addRoomRemove.addEventListener('click', () => {
                    removeImage(addImageState, 'room', addRoomPreview, addRoomError, addRoomCancel, addRoomFile);
                });
            }
            if (addCrRemove) {
                addCrRemove.addEventListener('click', () => {
                    removeImage(addImageState, 'cr', addCrPreview, addCrError, addCrCancel, addCrFile);
                });
            }
            if (addKitchenRemove) {
                addKitchenRemove.addEventListener('click', () => {
                    removeImage(addImageState, 'kitchen', addKitchenPreview, addKitchenError, addKitchenCancel, addKitchenFile);
                });
            }

            if (addRoomCancel) {
                addRoomCancel.addEventListener('click', () => {
                    cancelImage(addImageState, 'room', addRoomPreview, addRoomError, addRoomCancel, addRoomFile);
                });
            }
            if (addCrCancel) {
                addCrCancel.addEventListener('click', () => {
                    cancelImage(addImageState, 'cr', addCrPreview, addCrError, addCrCancel, addCrFile);
                });
            }
            if (addKitchenCancel) {
                addKitchenCancel.addEventListener('click', () => {
                    cancelImage(addImageState, 'kitchen', addKitchenPreview, addKitchenError, addKitchenCancel, addKitchenFile);
                });
            }

            const resetAddImages = () => {
                clearTempUrls(addImageState);
                addImageState.removed = {};
                addImageState.original = {
                    room: addRoomPreview?.dataset.fallback || '',
                    cr: addCrPreview?.dataset.fallback || '',
                    kitchen: addKitchenPreview?.dataset.fallback || '',
                };
                if (addRoomFile) addRoomFile.value = '';
                if (addCrFile) addCrFile.value = '';
                if (addKitchenFile) addKitchenFile.value = '';
                if (addRoomCancel) hideElement(addRoomCancel);
                if (addCrCancel) hideElement(addCrCancel);
                if (addKitchenCancel) hideElement(addKitchenCancel);
                if (addRoomError) hideElement(addRoomError);
                if (addCrError) hideElement(addCrError);
                if (addKitchenError) hideElement(addKitchenError);
                setImage(addRoomPreview, addImageState.original.room);
                setImage(addCrPreview, addImageState.original.cr);
                setImage(addKitchenPreview, addImageState.original.kitchen);
            };

            const resetAddForm = () => {
                if (addHouseName) addHouseName.value = '';
                if (addHouseMonthly) addHouseMonthly.value = '';
                if (addHouseLandlord) addHouseLandlord.value = '';
                if (addHouseAddress) addHouseAddress.value = '';
                if (addHouseDescription) addHouseDescription.value = '';
                clearMessage(addHouseModalError);
                resetAddImages();
            };

            const fillModal = (btn) => {
                activeButton = btn;
                activeRow = btn.closest('tr');

                document.getElementById('modalHouseName').textContent = btn.dataset.name ?? '';
                document.getElementById('modalHouseLandlord').textContent = btn.dataset.landlord ?? '';
                document.getElementById('modalHouseAddress').textContent = btn.dataset.address ?? '';
                document.getElementById('modalHouseStatus').textContent = btn.dataset.status ?? '';
                document.getElementById('modalHouseMonthly').textContent = btn.dataset.monthly ?? '';
                setImage(document.getElementById('modalHouseHero'), btn.dataset.image);
                setImage(document.getElementById('modalRoomImage'), btn.dataset.room);
                setImage(document.getElementById('modalCrImage'), btn.dataset.cr);
                setImage(document.getElementById('modalKitchenImage'), btn.dataset.kitchen);

                if (editName) editName.value = btn.dataset.name ?? '';
                if (editLandlord) editLandlord.value = btn.dataset.landlord ?? '';
                if (editAddress) editAddress.value = btn.dataset.address ?? '';
                if (editMonthly) editMonthly.value = btn.dataset.monthly ?? '';
                if (editStatus) editStatus.value = btn.dataset.status ?? '';

                clearTempUrls(imageState);
                imageState.removed = {};
                imageState.original = {
                    image: btn.dataset.image ?? '',
                    room: btn.dataset.room ?? '',
                    cr: btn.dataset.cr ?? '',
                    kitchen: btn.dataset.kitchen ?? '',
                };
                if (editHouseImageFile) editHouseImageFile.value = '';
                if (editRoomImageFile) editRoomImageFile.value = '';
                if (editCrImageFile) editCrImageFile.value = '';
                if (editKitchenImageFile) editKitchenImageFile.value = '';

                if (houseImageError) hideElement(houseImageError);
                if (roomImageError) hideElement(roomImageError);
                if (crImageError) hideElement(crImageError);
                if (kitchenImageError) hideElement(kitchenImageError);

                if (cancelHouseImage) hideElement(cancelHouseImage);
                if (cancelRoomImage) hideElement(cancelRoomImage);
                if (cancelCrImage) hideElement(cancelCrImage);
                if (cancelKitchenImage) hideElement(cancelKitchenImage);

                clearMessage(manageSaveError);
                clearMessage(manageSaveSuccess);

                const isManageMode = btn.dataset.mode === 'manage';
                if (manageSection) manageSection.classList.toggle('hidden', !isManageMode);
                if (saveButton) saveButton.classList.toggle('hidden', !isManageMode);
                if (viewDetails) viewDetails.classList.toggle('hidden', isManageMode);
                imageActions.forEach((el) => el.classList.toggle('hidden', !isManageMode));

                openOverlay(modal);
            };

            if (tableBody) {
                tableBody.addEventListener('click', (event) => {
                    const btn = event.target.closest('button[data-name][data-landlord][data-mode]');
                    if (btn) {
                        fillModal(btn);
                    }
                });
            }

            const closeModal = () => {
                closeOverlay(modal);
            };

            const updateRowDisplay = (row, data) => {
                if (!row) return;
                const cells = row.querySelectorAll('td');
                if (cells.length < 3) return;
                cells[0].textContent = data.name ?? '';
                cells[1].textContent = data.landlord ?? '';
                const addressCell = cells[2];
                if (addressCell) addressCell.textContent = data.address ?? '';
            };

            const syncRowButtons = (row, data) => {
                if (!row) return;
                row.querySelectorAll('button[data-name][data-landlord]').forEach((btn) => {
                    btn.dataset.name = data.name ?? '';
                    btn.dataset.landlord = data.landlord ?? '';
                    btn.dataset.address = data.address ?? '';
                    btn.dataset.status = data.status ?? '';
                    btn.dataset.image = data.image ?? '';
                    btn.dataset.room = data.room ?? '';
                    btn.dataset.cr = data.cr ?? '';
                    btn.dataset.kitchen = data.kitchen ?? '';
                    btn.dataset.monthly = data.monthly ?? '';
                });
            };

            let manageSaving = false;
            if (saveButton) {
                saveButton.addEventListener('click', async () => {
                    clearMessage(manageSaveError);
                    clearMessage(manageSaveSuccess);

                    if (manageSaving) return;

                    const updateUrl = activeButton?.dataset.updateUrl
                        || activeRow?.querySelector('button[data-update-url]')?.dataset.updateUrl
                        || '';
                    if (!updateUrl) {
                        showMessage(manageSaveError, 'Unable to save: missing update route.');
                        return;
                    }

                    const updated = {
                        name: (editName?.value || '').trim(),
                        landlord: (editLandlord?.value || '').trim(),
                        address: (editAddress?.value || '').trim(),
                        monthly: (editMonthly?.value || '').trim(),
                        status: (editStatus?.value || '').trim(),
                    };

                    const missingFields = [];
                    if (!updated.name) missingFields.push('Name');
                    if (!updated.landlord) missingFields.push('Landlord');
                    if (!updated.address) missingFields.push('Address');
                    if (!updated.monthly) missingFields.push('Monthly Payment');

                    if (missingFields.length) {
                        showMessage(manageSaveError, `Please fill in: ${missingFields.join(', ')}.`);
                        return;
                    }

                    const statusValue = updated.status.toLowerCase() === 'active' ? '1' : '0';
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    const formData = new FormData();
                    if (csrfToken) formData.append('_token', csrfToken);
                    formData.append('_method', 'PUT');
                    formData.append('name', updated.name);
                    formData.append('landlord_info', updated.landlord);
                    formData.append('address', updated.address);
                    formData.append('monthly_payment', updated.monthly);
                    formData.append('is_active', statusValue);

                    if (imageState.removed.image) formData.append('remove_exterior_image', '1');
                    if (imageState.removed.room) formData.append('remove_room_image', '1');
                    if (imageState.removed.cr) formData.append('remove_cr_image', '1');
                    if (imageState.removed.kitchen) formData.append('remove_kitchen_image', '1');

                    const exteriorFile = editHouseImageFile?.files?.[0];
                    const roomFile = editRoomImageFile?.files?.[0];
                    const crFile = editCrImageFile?.files?.[0];
                    const kitchenFile = editKitchenImageFile?.files?.[0];

                    if (exteriorFile) formData.append('exterior_image', exteriorFile);
                    if (roomFile) formData.append('room_image', roomFile);
                    if (crFile) formData.append('cr_image', crFile);
                    if (kitchenFile) formData.append('kitchen_image', kitchenFile);

                    manageSaving = true;
                    saveButton.disabled = true;
                    saveButton.classList.add('opacity-70', 'cursor-not-allowed');

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
                                showMessage(manageSaveError, errorText || 'Please check the highlighted fields.');
                            } else {
                                showMessage(manageSaveError, data?.message || 'Unable to save changes.');
                            }
                            return;
                        }

                        const house = data?.house || {};
                        const merged = {
                            name: house.name ?? updated.name,
                            landlord: house.landlord_info ?? updated.landlord,
                            address: house.address ?? updated.address,
                            monthly: house.monthly_payment ?? updated.monthly,
                            status: house.status_label ?? (statusValue === '1' ? 'Active' : 'Inactive'),
                            image: house.exterior_url ?? activeButton?.dataset.image ?? '',
                            room: house.room_url ?? activeButton?.dataset.room ?? '',
                            cr: house.cr_url ?? activeButton?.dataset.cr ?? '',
                            kitchen: house.kitchen_url ?? activeButton?.dataset.kitchen ?? '',
                        };

                        syncRowButtons(activeRow, merged);
                        updateRowDisplay(activeRow, merged);

                        setText(modalHouseName, merged.name);
                        setText(modalHouseLandlord, merged.landlord);
                        setText(modalHouseAddress, merged.address);
                        setText(modalHouseStatus, merged.status);
                        setText(modalHouseMonthly, merged.monthly);
                        setImage(document.getElementById('modalHouseHero'), merged.image);
                        setImage(document.getElementById('modalRoomImage'), merged.room);
                        setImage(document.getElementById('modalCrImage'), merged.cr);
                        setImage(document.getElementById('modalKitchenImage'), merged.kitchen);

                        imageState.original = {
                            image: merged.image,
                            room: merged.room,
                            cr: merged.cr,
                            kitchen: merged.kitchen,
                        };
                        clearTempUrls(imageState);
                        imageState.removed = {};
                        if (cancelHouseImage) hideElement(cancelHouseImage);
                        if (cancelRoomImage) hideElement(cancelRoomImage);
                        if (cancelCrImage) hideElement(cancelCrImage);
                        if (cancelKitchenImage) hideElement(cancelKitchenImage);
                        if (editHouseImageFile) editHouseImageFile.value = '';
                        if (editRoomImageFile) editRoomImageFile.value = '';
                        if (editCrImageFile) editCrImageFile.value = '';
                        if (editKitchenImageFile) editKitchenImageFile.value = '';
                        if (houseImageError) hideElement(houseImageError);
                        if (roomImageError) hideElement(roomImageError);
                        if (crImageError) hideElement(crImageError);
                        if (kitchenImageError) hideElement(kitchenImageError);

                        showMessage(manageSaveSuccess, 'Successfully saved.');
                        const modalBody = document.getElementById('houseModalBody');
                        if (modalBody) {
                            modalBody.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                        setTimeout(() => hideElement(manageSaveSuccess), 3000);
                    } catch (error) {
                        showMessage(manageSaveError, 'Something went wrong. Please try again.');
                    } finally {
                        manageSaving = false;
                        saveButton.disabled = false;
                        saveButton.classList.remove('opacity-70', 'cursor-not-allowed');
                    }
                });
            }

            const createActionButton = (label, mode, primary, house) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = primary
                    ? 'inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700'
                    : 'inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold text-gray-700 hover:bg-gray-50';
                btn.dataset.name = house.name || '';
                btn.dataset.landlord = house.landlord_info || '';
                btn.dataset.address = house.address || '';
                btn.dataset.status = house.status_label || (house.is_active ? 'Active' : 'Inactive');
                btn.dataset.image = house.exterior_url || '';
                btn.dataset.room = house.room_url || '';
                btn.dataset.cr = house.cr_url || '';
                btn.dataset.kitchen = house.kitchen_url || '';
                btn.dataset.monthly = house.monthly_payment || '';
                if (house.update_url) {
                    btn.dataset.updateUrl = house.update_url;
                }
                btn.dataset.mode = mode;
                const span = document.createElement('span');
                span.textContent = label;
                btn.appendChild(span);
                return btn;
            };

            const appendHouseRow = (house) => {
                if (!tableBody) return;
                const emptyCell = tableBody.querySelector('td[colspan="4"]');
                if (emptyCell) {
                    emptyCell.closest('tr')?.remove();
                }

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const nameCell = document.createElement('td');
                nameCell.className = 'px-5 py-3 font-semibold text-gray-900';
                nameCell.textContent = house.name || '';

                const landlordCell = document.createElement('td');
                landlordCell.className = 'px-5 py-3 text-gray-700';
                landlordCell.textContent = house.landlord_info || '';

                const addressCell = document.createElement('td');
                addressCell.className = 'px-5 py-3 text-gray-700';
                addressCell.textContent = house.address || '';

                const actionCell = document.createElement('td');
                actionCell.className = 'px-5 py-3 text-right';
                const actions = document.createElement('div');
                actions.className = 'flex justify-end items-center gap-2';

                const viewBtn = createActionButton('View', 'view', false, house);
                const manageBtn = createActionButton('Manage', 'manage', true, house);

                actions.appendChild(viewBtn);
                actions.appendChild(manageBtn);
                actionCell.appendChild(actions);

                row.appendChild(nameCell);
                row.appendChild(landlordCell);
                row.appendChild(addressCell);
                row.appendChild(actionCell);

                tableBody.prepend(row);
            };

            let addSaving = false;
            if (saveAddHouseButton) {
                saveAddHouseButton.addEventListener('click', async () => {
                    clearMessage(addHouseModalError);
                    clearMessage(addHouseError);

                    if (addSaving) return;
                    if (!addStoreUrl) {
                        showMessage(addHouseModalError, 'Unable to save: missing route configuration.');
                        return;
                    }

                    const missingFields = [];
                    if (!addHouseName?.value.trim()) missingFields.push('Name');
                    if (!addHouseMonthly?.value.trim()) missingFields.push('Monthly Payment');
                    if (!addHouseLandlord?.value.trim()) missingFields.push('Landlord');
                    if (!addHouseAddress?.value.trim()) missingFields.push('Address');
                    if (!addHouseDescription?.value.trim()) missingFields.push('Description');

                    if (missingFields.length) {
                        showMessage(addHouseModalError, `Please fill in: ${missingFields.join(', ')}.`);
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const formData = new FormData();
                    if (csrfToken) formData.append('_token', csrfToken);
                    formData.append('name', addHouseName.value.trim());
                    formData.append('monthly_payment', addHouseMonthly.value.trim());
                    formData.append('landlord_info', addHouseLandlord.value.trim());
                    formData.append('address', addHouseAddress.value.trim());
                    formData.append('description', addHouseDescription.value.trim());
                    formData.append('is_active', '1');

                    const roomFile = addRoomFile?.files?.[0];
                    const crFile = addCrFile?.files?.[0];
                    const kitchenFile = addKitchenFile?.files?.[0];

                    if (roomFile && !addImageState.removed.room) {
                        formData.append('room_image', roomFile);
                    }
                    if (crFile && !addImageState.removed.cr) {
                        formData.append('cr_image', crFile);
                    }
                    if (kitchenFile && !addImageState.removed.kitchen) {
                        formData.append('kitchen_image', kitchenFile);
                    }

                    addSaving = true;
                    saveAddHouseButton.disabled = true;
                    saveAddHouseButton.classList.add('opacity-70');
                    saveAddHouseButton.classList.add('cursor-not-allowed');

                    try {
                        const response = await fetch(addStoreUrl, {
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
                                showMessage(addHouseModalError, errorText || 'Please check the highlighted fields.');
                            } else {
                                showMessage(addHouseModalError, data?.message || 'Unable to save boarding house.');
                            }
                            return;
                        }

                        if (data?.house) {
                            appendHouseRow(data.house);
                        }

                        closeOverlay(addModal);
                        showMessage(addHouseSuccess, 'Successfully saved.');
                        setTimeout(() => hideElement(addHouseSuccess), 4000);
                    } catch (error) {
                        showMessage(addHouseModalError, 'Something went wrong. Please try again.');
                    } finally {
                        addSaving = false;
                        saveAddHouseButton.disabled = false;
                        saveAddHouseButton.classList.remove('opacity-70');
                        saveAddHouseButton.classList.remove('cursor-not-allowed');
                    }
                });
            }

            closeButtons.forEach((btn) => btn?.addEventListener('click', closeModal));
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            }

            const openAddModal = () => {
                resetAddForm();
                openOverlay(addModal);
                addHouseName?.focus();
            };

            const closeAddModal = () => {
                closeOverlay(addModal);
            };

            if (openAddButton) {
                openAddButton.addEventListener('click', openAddModal);
            }

            addModalCloseButtons.forEach((btn) => btn?.addEventListener('click', closeAddModal));
            if (addModal) {
                addModal.addEventListener('click', (e) => {
                    if (e.target === addModal) closeAddModal();
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                if (addModal && !addModal.classList.contains('hidden')) {
                    closeAddModal();
                    return;
                }
                if (modal && !modal.classList.contains('hidden')) {
                    closeModal();
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/owner/boarding-houses.blade.php ENDPATH**/ ?>