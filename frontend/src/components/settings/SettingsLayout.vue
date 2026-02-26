<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">الإعدادات</h1>
                <p class="text-gray-500 dark:text-gray-400">إدارة إعدادات النظام وتفضيلات التطبيق</p>
            </div>
            <slot name="header-actions"></slot>
        </div>

        <!-- Content -->
        <div class="min-h-[400px]">
            <slot></slot>
        </div>
    </div>
</template>
