<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['main-class' => 'w-full']); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tenant Management</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <?php if(session('success')): ?>
            <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden w-full">
            <div class="p-5 border-b border-gray-100">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-800">All Tenants</h3>
                    <button id="openArchiveModal" class="text-sm text-gray-500 hover:text-gray-700" type="button" title="View archived users">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M5 7v11c0 .828.672 1.5 1.5 1.5h11c.828 0 1.5-.672 1.5-1.5V7M9 7v-3h6v3" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 12h4m-2-2v4" />
                        </svg>
                    </button>
                </div>

                <form method="GET" action="<?php echo e(route('admin.users')); ?>" class="mt-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_220px_auto] gap-3">
                    <label class="sr-only" for="tenant-search">Search tenants</label>
                    <input
                        id="tenant-search"
                        type="text"
                        name="q"
                        value="<?php echo e($search ?? ''); ?>"
                        placeholder="Search name, email, phone, boardinghouse, room"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    <label class="sr-only" for="status-filter">Status</label>
                    <select
                        id="status-filter"
                        name="status"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="all" <?php if(($status ?? 'all') === 'all'): echo 'selected'; endif; ?>>All Status</option>
                        <option value="approved" <?php if(($status ?? '') === 'approved'): echo 'selected'; endif; ?>>Approved</option>
                        <option value="pending" <?php if(($status ?? '') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                            Apply
                        </button>
                        <a href="<?php echo e(route('admin.users')); ?>" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full min-w-[980px] table-fixed text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                        <tr>
                            <th class="w-[14%] px-5 py-3 text-center">Profile</th>
                            <th class="w-[46%] px-5 py-3 text-left">Info</th>
                            <th class="w-[10%] px-5 py-3 text-left">Room No.</th>
                            <th class="w-[15%] px-5 py-3 text-left">Status</th>
                            <th class="w-[15%] px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tenantTableBody" class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $statusLabel = $user->is_active ? 'Approved' : 'Pending';
                                $statusClasses = [
                                    'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'Pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                ][$statusLabel] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                $profileImageUrl = $user->profile_image
                                    ? \Illuminate\Support\Facades\Storage::url($user->profile_image)
                                    : asset('images/avatar-placeholder.svg');
                                $avatarFallback = asset('images/avatar-placeholder.svg');
                                $phone = trim((string) ($user->phone ?? '')) ?: null;
                                $boardinghouseName = trim((string) ($user->institution_name ?? '')) ?: null;
                                $fullAddress = trim((string) ($user->boardinghouse_address ?? '')) ?: null;
                                $roomNumber = trim((string) ($user->room_number ?? '')) ?: null;
                                $addressValue = $fullAddress ?: ($boardinghouseName ?: null);
                            ?>
                            <tr
                                class="tenant-row hover:bg-gray-50"
                            >
                                <td class="px-5 py-4 align-top">
                                    <div class="flex justify-center">
                                        <div
                                            class="h-[80px] w-[80px] shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100"
                                            style="flex: 0 0 80px;"
                                        >
                                            <img
                                                src="<?php echo e($profileImageUrl); ?>"
                                                alt="<?php echo e($user->name); ?> profile image"
                                                class="h-[80px] w-[80px] rounded-full object-cover"
                                                onerror="this.onerror=null;this.src='<?php echo e($avatarFallback); ?>';"
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="min-w-0 space-y-1 text-sm text-gray-700">
                                        <p class="break-words"><span class="font-semibold text-gray-900">Full Name:</span> <?php echo e($user->name); ?></p>
                                        <p class="break-all"><span class="font-semibold text-gray-900">Email:</span> <?php echo e($user->email); ?></p>
                                        <p class="break-words"><span class="font-semibold text-gray-900">Phone Number:</span> <?php echo e($phone ?? 'N/A'); ?></p>
                                        <p class="break-words"><span class="font-semibold text-gray-900">Address:</span> <?php echo e($addressValue ?? 'N/A'); ?></p>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top text-sm text-gray-700">
                                    <?php echo e($roomNumber ?: 'N/A'); ?>

                                </td>
                                <td class="px-5 py-4 align-top">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border <?php echo e($statusClasses); ?>" data-status-badge>
                                        <?php echo e($statusLabel); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right align-top">
                                    <div class="flex flex-wrap justify-end gap-2" data-no-row-open="1">
                                        <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="px-3 py-1 rounded-lg border border-amber-200 bg-amber-50 text-amber-600 text-xs font-semibold uppercase tracking-wide hover:bg-amber-100">
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            class="px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold uppercase tracking-wide hover:bg-rose-100 delete-user-btn"
                                            data-delete-url="<?php echo e(route('admin.users.destroy', $user)); ?>"
                                            data-user-name="<?php echo e($user->name); ?>"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr id="noTenantRow">
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">
                                    No tenants found for the current filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-gray-500" id="tenantPaginationSummary">
                    <?php if($users->total() > 0): ?>
                        Showing <?php echo e($users->firstItem()); ?> to <?php echo e($users->lastItem()); ?> of <?php echo e($users->total()); ?> tenants
                    <?php else: ?>
                        Showing 0 tenants
                    <?php endif; ?>
                </p>
                <div>
                    <?php echo e($users->withQueryString()->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <div id="archiveModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-md shadow-xl w-[min(95vw,720px)] max-w-[720px] mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Archived Tenants</h3>
                <button id="closeArchiveModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none" aria-label="Close">&times;</button>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm">
                <?php if($archivedUsers->count()): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Name</th>
                                    <th class="px-4 py-3 text-left">Email</th>
                                    <th class="px-4 py-3 text-left">Role</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <?php $__currentLoopData = $archivedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $archivedUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($archivedUser->name); ?></td>
                                        <td class="px-4 py-3 text-gray-600"><?php echo e($archivedUser->email); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                <?php echo e($archivedUser->roles->pluck('name')->first() ?? $archivedUser->role ?? 'tenant'); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                <form action="<?php echo e(route('admin.users.restore', $archivedUser)); ?>" method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <button type="submit" class="px-3 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 text-xs font-semibold uppercase tracking-wide hover:bg-emerald-100">
                                                        Restore
                                                    </button>
                                                </form>
                                                <form action="<?php echo e(route('admin.users.destroy', $archivedUser)); ?>" method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="button" class="archive-delete-trigger px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 text-xs font-semibold uppercase tracking-wide hover:bg-rose-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-4">
                        <?php echo e($archivedUsers->withQueryString()->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="text-sm text-gray-500">
                        No archived users yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="archiveDeleteConfirm" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-60">
        <div class="bg-white rounded-lg px-6 py-5 shadow-xl text-center text-sm font-semibold text-gray-900">
            <p class="mb-3">Delete this item permanently?</p>
            <div class="flex justify-center gap-2">
                <button id="archiveDeleteNo" class="px-4 py-2 rounded-md border border-gray-200 text-gray-700 hover:bg-gray-50">No</button>
                <button id="archiveDeleteYes" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Yes</button>
            </div>
        </div>
    </div>

    <div id="tenantDeleteConfirm" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-60">
        <div class="bg-white rounded-lg px-6 py-5 shadow-xl w-[min(92vw,420px)]">
            <h3 class="text-base font-semibold text-gray-900">Delete Tenant</h3>
            <p id="tenantDeleteMessage" class="mt-2 text-sm text-gray-600">Delete this tenant permanently?</p>
            <div class="mt-4 flex justify-end gap-2">
                <button id="tenantDeleteNo" class="px-4 py-2 rounded-md border border-gray-200 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button id="tenantDeleteYes" class="px-4 py-2 rounded-md bg-rose-600 text-white hover:bg-rose-700">Delete</button>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const archiveModal = document.getElementById('archiveModal');
        const archiveTrigger = document.getElementById('openArchiveModal');
        const archiveClose = document.getElementById('closeArchiveModal');
        const tenantTableBody = document.getElementById('tenantTableBody');

        const tenantDeleteModal = document.getElementById('tenantDeleteConfirm');
        const tenantDeleteMessage = document.getElementById('tenantDeleteMessage');
        const tenantDeleteYes = document.getElementById('tenantDeleteYes');
        const tenantDeleteNo = document.getElementById('tenantDeleteNo');

        const archiveDeleteModal = document.getElementById('archiveDeleteConfirm');
        const archiveDeleteYes = document.getElementById('archiveDeleteYes');
        const archiveDeleteNo = document.getElementById('archiveDeleteNo');

        let pendingTenantDelete = null;
        let pendingArchiveDeleteForm = null;

        const isOverlay = (target, overlay) => target === overlay;
        const isVisible = (el) => !!el && !el.classList.contains('hidden');
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

        archiveTrigger?.addEventListener('click', () => openOverlay(archiveModal));
        archiveClose?.addEventListener('click', () => closeOverlay(archiveModal));
        archiveModal?.addEventListener('click', (event) => {
            if (isOverlay(event.target, archiveModal)) {
                closeOverlay(archiveModal);
            }
        });

        const ensureTenantEmptyRow = () => {
            if (!tenantTableBody) return;
            if (tenantTableBody.querySelector('.tenant-row')) return;
            if (tenantTableBody.querySelector('#noTenantRow')) return;

            const row = document.createElement('tr');
            row.id = 'noTenantRow';
            row.innerHTML = '<td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">No tenants found for the current filters.</td>';
            tenantTableBody.appendChild(row);
        };

        const closeTenantDelete = () => {
            pendingTenantDelete = null;
            closeOverlay(tenantDeleteModal);
        };

        const requestTenantDelete = (button) => {
            const deleteUrl = button.dataset.deleteUrl || '';
            if (!deleteUrl) return;
            const name = button.dataset.userName || 'this tenant';
            const row = button.closest('.tenant-row');
            pendingTenantDelete = { deleteUrl, row, name };
            if (tenantDeleteMessage) {
                tenantDeleteMessage.textContent = `Delete ${name} permanently?`;
            }
            openOverlay(tenantDeleteModal);
        };

        const performTenantDelete = async () => {
            if (!pendingTenantDelete) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const formData = new FormData();
            if (csrfToken) formData.append('_token', csrfToken);
            formData.append('_method', 'DELETE');

            tenantDeleteYes.disabled = true;
            tenantDeleteYes.classList.add('opacity-70', 'cursor-not-allowed');

            try {
                const response = await fetch(pendingTenantDelete.deleteUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    alert(data?.message || 'Unable to delete tenant.');
                    return;
                }

                pendingTenantDelete.row?.remove();
                ensureTenantEmptyRow();
                closeTenantDelete();
            } catch (error) {
                alert('Unable to delete tenant.');
            } finally {
                tenantDeleteYes.disabled = false;
                tenantDeleteYes.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        };

        tenantDeleteNo?.addEventListener('click', closeTenantDelete);
        tenantDeleteYes?.addEventListener('click', performTenantDelete);
        tenantDeleteModal?.addEventListener('click', (event) => {
            if (isOverlay(event.target, tenantDeleteModal)) {
                closeTenantDelete();
            }
        });

        tenantTableBody?.addEventListener('click', (event) => {
            const deleteButton = event.target.closest('.delete-user-btn');
            if (deleteButton) {
                requestTenantDelete(deleteButton);
                return;
            }

            if (event.target.closest('a,button,form,[data-no-row-open]')) {
                return;
            }
        });

        document.querySelectorAll('.archive-delete-trigger').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const form = button.closest('form');
                if (!form) return;
                pendingArchiveDeleteForm = form;
                openOverlay(archiveDeleteModal);
            });
        });

        const closeArchiveDelete = () => {
            pendingArchiveDeleteForm = null;
            closeOverlay(archiveDeleteModal);
        };

        archiveDeleteNo?.addEventListener('click', closeArchiveDelete);
        archiveDeleteYes?.addEventListener('click', () => {
            if (pendingArchiveDeleteForm) {
                pendingArchiveDeleteForm.requestSubmit();
            }
            closeArchiveDelete();
        });
        archiveDeleteModal?.addEventListener('click', (event) => {
            if (isOverlay(event.target, archiveDeleteModal)) {
                closeArchiveDelete();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            if (isVisible(tenantDeleteModal)) {
                closeTenantDelete();
                return;
            }

            if (isVisible(archiveDeleteModal)) {
                closeArchiveDelete();
                return;
            }

            if (isVisible(archiveModal)) {
                closeOverlay(archiveModal);
                return;
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/admin/users.blade.php ENDPATH**/ ?>