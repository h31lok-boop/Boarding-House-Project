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
        $stats = [
            ['label' => 'Total Rooms', 'value' => '12', 'icon' => '🏠', 'color' => 'blue'],
            ['label' => 'Occupancy Rate', 'value' => '85%', 'icon' => '📊', 'color' => 'green'],
            ['label' => 'Pending Requests', 'value' => '4', 'icon' => '⏳', 'color' => 'yellow'],
            ['label' => 'Monthly Revenue', 'value' => '₱45,000', 'icon' => '💰', 'color' => 'indigo'],
        ];

        $properties = [
            [
                'id' => 1,
                'name' => 'Room 101 - Master Suite',
                'type' => 'Single',
                'price' => '₱5,000/mo',
                'status' => 'Occupied',
                'image' => 'https://images.unsplash.com/photo-1522771753035-484980f8a323?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
            ],
            [
                'id' => 2,
                'name' => 'Room 102 - Shared A',
                'type' => 'Double Deck',
                'price' => '₱2,500/mo',
                'status' => 'Available',
                'image' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
            ],
            [
                'id' => 3,
                'name' => 'Room 103 - Shared B',
                'type' => 'Double Deck',
                'price' => '₱2,500/mo',
                'status' => 'Maintenance',
                'image' => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
            ],
        ];

        $bookings = [
            ['student' => 'Juan Dela Cruz', 'room' => 'Room 102', 'date' => 'Oct 24, 2023', 'status' => 'Pending'],
            ['student' => 'Maria Clara', 'room' => 'Room 104', 'date' => 'Oct 23, 2023', 'status' => 'Approved'],
            ['student' => 'Jose Rizal', 'room' => 'Room 101', 'date' => 'Oct 20, 2023', 'status' => 'Active'],
            ['student' => 'Andres B.', 'room' => 'Room 102', 'date' => 'Oct 19, 2023', 'status' => 'Rejected'],
        ];
    ?>

     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Admin Dashboard')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center">
                        <div class="p-3 rounded-full bg-<?php echo e($stat['color']); ?>-100 text-<?php echo e($stat['color']); ?>-500 mr-4 text-2xl">
                            <?php echo e($stat['icon']); ?>

                        </div>
                        <div>
                            <div class="text-gray-500 text-sm"><?php echo e($stat['label']); ?></div>
                            <div class="text-2xl font-bold text-gray-800"><?php echo e($stat['value']); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Booking Requests</h3>
                            <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm whitespace-nowrap">
                                <thead class="uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4">Student</th>
                                        <th scope="col" class="px-6 py-4">Room Interest</th>
                                        <th scope="col" class="px-6 py-4">Move-in Date</th>
                                        <th scope="col" class="px-6 py-4">Status</th>
                                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo e($booking['student']); ?></td>
                                            <td class="px-6 py-4 text-gray-500"><?php echo e($booking['room']); ?></td>
                                            <td class="px-6 py-4 text-gray-500"><?php echo e($booking['date']); ?></td>
                                            <td class="px-6 py-4">
                                                <?php if($booking['status'] === 'Pending'): ?>
                                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                                                <?php elseif($booking['status'] === 'Approved' || $booking['status'] === 'Active'): ?>
                                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e($booking['status']); ?></span>
                                                <?php else: ?>
                                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e($booking['status']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <?php if($booking['status'] === 'Pending'): ?>
                                                    <button class="text-green-600 hover:text-green-900 mr-2 font-bold">✓</button>
                                                    <button class="text-red-600 hover:text-red-900 font-bold">✕</button>
                                                <?php else: ?>
                                                    <span class="text-gray-400 text-xs">--</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Room Inventory</h3>
                            <button class="text-sm bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">+ Add</button>
                        </div>

                        <div class="space-y-4">
                            <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex gap-4 border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                    <div class="w-20 h-20 flex-shrink-0">
                                        <img src="<?php echo e($property['image']); ?>" alt="Room" class="w-full h-full object-cover rounded-md">
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900"><?php echo e($property['name']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo e($property['type']); ?> • <?php echo e($property['price']); ?></p>
                                        <div class="mt-2">
                                            <?php if($property['status'] === 'Available'): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Available
                                                </span>
                                            <?php elseif($property['status'] === 'Occupied'): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Occupied
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    <?php echo e($property['status']); ?>

                                                </span>
                                            <?php endif; ?>
                                            <button class="float-right text-xs text-blue-600 hover:text-blue-800">Edit</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                            <a href="#" class="text-sm text-gray-500 hover:text-gray-700">View Full Inventory &rarr;</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
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
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views\dashboard.blade.php ENDPATH**/ ?>