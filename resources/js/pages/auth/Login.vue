<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

import { ref } from 'vue';
const showPassword = ref(false);
</script>

<template>
    <AuthBase
        title="Sign In"
        description="Enter your email and password to sign in!"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
            novalidate
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-sm font-semibold text-slate-700">Email<span class="text-blue-600 ml-1">*</span></Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="info@gmail.com"
                        class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 text-base focus-visible:ring-4 focus-visible:ring-blue-500/10 focus-visible:border-blue-600 transition-all"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-sm font-semibold text-slate-700">Password<span class="text-blue-600 ml-1">*</span></Label>
                    <div class="relative">
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="h-12 rounded-xl border-slate-200 bg-slate-50/50 px-4 pr-11 text-base focus-visible:ring-4 focus-visible:ring-blue-500/10 focus-visible:border-blue-600 transition-all"
                        />
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 focus:outline-none transition-all rounded-lg"
                            tabindex="-1"
                        >
                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 text-sm text-slate-600 font-normal">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Keep me logged in</span>
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm text-blue-600 font-medium"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>

                <Button
                    type="submit"
                    class="mt-6 w-full h-12 text-base font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md hover:shadow-lg transition-all"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Sign In
                </Button>
            </div>

            <div
                class="text-left text-sm text-slate-600 font-medium"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5" class="text-blue-600 font-medium">Sign Up</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
