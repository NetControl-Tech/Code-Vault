<template>
    <AuthCorePage :title="'التحقق الثنائي'" :subtitle="'الرجاء إدخال رمز التحقق للحفاظ على أمان حسابك'">
        <form class="space-y-6" @submit.prevent="verify2faCode">
            <!-- Global Error Message -->
            <Transition name="fade">
                <div v-if="errorMsg"
                    class="flex items-center justify-between py-3 px-4 bg-red-500/10 border border-red-500/50 text-red-200 rounded-2xl animate-shake"
                    role="alert">
                    <div class="flex items-center">
                        <ExclamationTriangleIcon class="h-5 w-5 ml-2 shrink-0 text-red-400" />
                        <span class="text-sm font-medium">{{ errorMsg }}</span>
                    </div>
                    <button @click="errorMsg = ''" type="button"
                        class="mr-auto p-1 rounded-full hover:bg-red-500/20 transition-colors"
                        aria-label="Dismiss error">
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </Transition>

            <!-- Global Success Message -->
            <Transition name="fade">
                <div v-if="successMsg"
                    class="flex items-center justify-between py-3 px-4 bg-green-500/10 border border-green-500/50 text-green-200 rounded-2xl"
                    role="alert">
                    <div class="flex items-center">
                        <span class="text-sm font-medium">{{ successMsg }}</span>
                    </div>
                    <button @click="successMsg = ''" type="button"
                        class="mr-auto p-1 rounded-full hover:bg-green-500/20 transition-colors"
                        aria-label="Dismiss message">
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </Transition>

            <div class="space-y-5">
                <!-- Code Field -->
                <div>
                    <label for="2fa-code" class="block text-sm font-medium text-slate-300 mb-2">
                        رمز التحقق
                    </label>
                    <div class="relative group/input flex justify-center">
                        <input id="2fa-code" name="code" type="text" autocomplete="one-time-code" required
                            v-model="code2fa" maxlength="6" pattern="[0-9]*" inputmode="numeric" :class="[
                                'block w-full tracking-[1em] text-center text-xl font-bold py-3.5 bg-slate-800/50 border rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all duration-300',
                                errorMsg ? 'border-red-500/50 bg-red-500/5' : 'border-slate-700 hover:border-slate-600'
                            ]" placeholder="••••••" dir="ltr" />
                    </div>
                    <p class="mt-2 text-xs text-slate-400 text-center">لقد أرسلنا رمزاً مكوناً من 6 أرقام إلى بريدك
                        الإلكتروني.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" :disabled="loading"
                    class="group/btn relative w-full flex justify-center py-4 px-4 text-sm font-bold rounded-2xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-xl shadow-indigo-500/20 hover:shadow-indigo-500/40 active:scale-[0.98]">
                    <span v-if="loading" class="ml-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                    {{ loading ? 'جاري التحقق...' : 'تأكيد الرمز' }}
                    <ArrowRightIcon v-if="!loading"
                        class="mr-2 h-4 w-4 transform group-hover/btn:-translate-x-1 transition-transform rotate-180" />
                </button>
            </div>

            <!-- Resend Button -->
            <div class="text-center mt-4">
                <button type="button" @click="resendCode" :disabled="countdown > 0 || loading"
                    class="text-sm font-medium text-indigo-400 hover:text-indigo-300 focus:outline-none disabled:text-slate-500 disabled:cursor-not-allowed transition-colors">
                    {{ countdown > 0 ? `إعادة إرسال الرمز خلال ${countdown} ثانية` : 'إعادة إرسال الرمز المقترح' }}
                </button>
            </div>

            <!-- Cancel Button -->
            <div class="text-center mt-2">
                <button type="button" @click="cancel" :disabled="loading"
                    class="text-sm font-medium text-red-500 hover:text-red-400 focus:outline-none disabled:text-slate-500 transition-colors">
                    إلغاء والعودة لتسجيل الدخول
                </button>
            </div>
        </form>
    </AuthCorePage>
</template>

<script setup>
import {
    ExclamationTriangleIcon,
    XMarkIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'
import { ref, onMounted } from "vue"
import router from '../router';
import { useAuthStore } from '../stores/auth';
import AuthCorePage from './AuthCorePage.vue';

const authStore = useAuthStore();

const loading = ref(false)
const errorMsg = ref("")
const successMsg = ref("")
const code2fa = ref('')
const countdown = ref(60)
let timer = null

function startCountdown() {
    countdown.value = 60;
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(timer);
        }
    }, 1000);
}

onMounted(() => {
    startCountdown()
})

function verify2faCode() {
    if (!code2fa.value || code2fa.value.length !== 6) {
        errorMsg.value = "يرجى إدخال رمز صحيح مكون من 6 أرقام.";
        return;
    }

    loading.value = true;
    errorMsg.value = "";
    successMsg.value = "";

    authStore.verify2fa(code2fa.value)
        .then((result) => {
            if (result.success) {
                router.push({ name: 'users' });
            } else {
                errorMsg.value = result.message || "رمز التحقق غير صحيح.";
            }
        })
        .finally(() => {
            loading.value = false;
        });
}

function resendCode() {
    loading.value = true;
    errorMsg.value = "";
    successMsg.value = "";

    authStore.resend2fa()
        .then((result) => {
            if (result.success) {
                successMsg.value = result.message || "تم إرسال رمز التحقق مجدداً.";
                startCountdown();
            } else {
                errorMsg.value = result.message || "حدث خطأ أثناء إعادة إرسال الرمز.";
            }
        })
        .finally(() => {
            loading.value = false;
        });
}

function cancel() {
    loading.value = true;
    errorMsg.value = "";
    successMsg.value = "";

    authStore.cancel2fa()
        .then(() => {
            router.push({ name: 'login' });
        })
        .finally(() => {
            loading.value = false;
        });
}
</script>
