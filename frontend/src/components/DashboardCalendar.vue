<template>
    <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                الزيارات القادمة
            </h3>
            <div class="flex items-center gap-4">
                <button @click="prevMonth"
                    class="p-2 hover:bg-slate-100 dark:hover:bg-dark-700 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:rotate-180" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <span class="text-lg font-medium min-w-[120px] text-center">{{ currentMonthName }} {{ currentYear
                }}</span>
                <button @click="nextMonth"
                    class="p-2 hover:bg-slate-100 dark:hover:bg-dark-700 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:rotate-180" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <div
            class="grid grid-cols-7 gap-px bg-slate-200 dark:bg-dark-700 border border-slate-200 dark:border-dark-700 rounded-lg overflow-hidden">
            <!-- Headers -->
            <div v-for="day in weekDays" :key="day"
                class="bg-slate-50 dark:bg-dark-800 p-2 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ day }}
            </div>

            <!-- Days -->
            <div v-for="(day, index) in calendarDays" :key="index" :class="[
                'min-h-[100px] p-2 bg-white dark:bg-dark-800 transition-colors',
                day.isToday ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''
            ]">
                <div v-if="day.date" class="h-full flex flex-col">
                    <span :class="[
                        'text-sm font-medium w-6 h-6 flex items-center justify-center rounded-full mb-1',
                        day.isToday ? 'bg-primary-600 text-white' : 'text-slate-700 dark:text-slate-300'
                    ]">{{ day.date }}</span>
                    <div class="flex-1 space-y-1 overflow-y-auto max-h-[80px] custom-scrollbar">
                        <router-link v-for="event in day.events" :key="event.id" :to="event.url" :class="[
                            'block text-[10px] px-1.5 py-0.5 rounded truncate transition-opacity hover:opacity-80 text-white',
                            event.className
                        ]" :title="event.title">
                            {{ event.extendedProps.client_name }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex gap-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-purple-500"></span>
                <span class="text-slate-600 dark:text-slate-400">مشورة ما قبل الزواج</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-pink-500"></span>
                <span class="text-slate-600 dark:text-slate-400">مشورة الحوامل</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-sky-500"></span>
                <span class="text-slate-600 dark:text-slate-400">مشورة الأطفال</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useDashboardStore } from '../stores/dashboard'

const dashboardStore = useDashboardStore()
const currentDate = ref(new Date())
const events = ref([])
const loading = ref(false)

const monthNames = [
    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
]

const currentMonthName = computed(() => {
    return monthNames[currentDate.value.getMonth()]
})

const currentYear = computed(() => {
    return currentDate.value.getFullYear()
})

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    return new Date(year, month + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    // We want Monday to be 0 or Saturday to be 0? 
    // In Egypt/Arab world, week often starts on Saturday.
    // Let's assume Saturday start for this app based on context, or standard Sunday.
    // Let's use Saturday start = 0.
    const day = new Date(year, month, 1).getDay()
    return (day + 1) % 7
})

const calendarDays = computed(() => {
    const days = []

    // Previous month filler
    for (let i = 0; i < firstDayOfMonth.value; i++) {
        days.push({ date: null, isCurrentMonth: false })
    }

    // Current month days
    for (let i = 1; i <= daysInMonth.value; i++) {
        const dateString = `${currentYear.value}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`
        const dayEvents = events.value.filter(e => e.start === dateString)
        days.push({
            date: i,
            fullDate: dateString,
            isCurrentMonth: true,
            events: dayEvents,
            isToday: dateString === new Date().toISOString().slice(0, 10)
        })
    }

    return days
})

function prevMonth() {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

function nextMonth() {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

function fetchEvents() {
    loading.value = true
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth() + 1
    const start = `${year}-${String(month).padStart(2, '0')}-01`
    const end = `${year}-${String(month).padStart(2, '0')}-${daysInMonth.value}`

    dashboardStore.fetchCalendar(start, end)
        .then(result => {
            if (result.success) {
                events.value = result.data
            }
        })
        .finally(() => {
            loading.value = false
        })
}

watch(currentDate, () => {
    fetchEvents()
})

onMounted(() => {
    fetchEvents()
})

const weekDays = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة']

</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 2px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #475569;
}
</style>
