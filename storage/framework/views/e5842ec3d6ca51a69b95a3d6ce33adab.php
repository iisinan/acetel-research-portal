<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="font-sans antialiased text-gray-900 bg-[#EAF8F1]">
        <div class="min-h-screen flex flex-col md:flex-row">
            <!-- Sidebar -->
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                
                <!-- Page Heading (Optional, mostly for mobile or small titles) -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                    <header class="md:hidden bg-white shadow-sm px-4 py-4">
                        <div class="font-bold text-xl text-gray-800">
                            <?php echo e($header); ?>

                        </div>
                    </header>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Main Content container -->
                <main class="flex-1 p-4 md:p-8">
                    <!-- The large rounded inner white card -->
                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 min-h-full p-6 md:p-10 relative overflow-hidden">
                        
                        <!-- Top decorative header inside the card (optional) -->
                        <div class="hidden md:flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                                <div class="text-2xl font-extrabold text-gray-800 tracking-tight">
                                    <?php echo e($header); ?>

                                </div>
                            <?php else: ?>
                                <div class="text-2xl font-extrabold text-gray-800 tracking-tight">
                                    <?php echo e(Auth::user()->roles->first()->name ?? 'Student'); ?> Dashboard
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <!-- Date / Quick actions -->
                            <div class="text-sm font-medium text-gray-500 bg-gray-50 px-4 py-2 rounded-full">
                                <?php echo e(now()->format('l, jS F Y')); ?>

                            </div>
                        </div>

                        <!-- Actual Page Content -->
                        <?php echo e($slot); ?>

                    </div>
                </main>
            </div>
        </div>
        
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\Documents\Herd\Thesis Monitoring\resources\views/layouts/app.blade.php ENDPATH**/ ?>