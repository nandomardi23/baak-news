<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <!-- Profile Information Card -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                     <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg width="120" height="120" viewBox="0 0 24 24" fill="currentColor" class="text-blue-600">
                             <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>

                    <div class="relative z-10">
                        <HeadingSmall
                            title="Profile Information"
                            description="Update your account's profile information and email address."
                            class="mb-6"
                        />

                        <Form
                            v-bind="ProfileController.update.form()"
                            class="max-w-xl space-y-6"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <Label for="name" class="text-slate-600 font-medium">Full Name</Label>
                                    <Input
                                        id="name"
                                        class="mt-1 block w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 focus:bg-white transition-all"
                                        name="name"
                                        :default-value="user.name"
                                        required
                                        autocomplete="name"
                                        placeholder="Enter your full name"
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email" class="text-slate-600 font-medium">Email Address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 focus:bg-white transition-all"
                                        name="email"
                                        :default-value="user.email"
                                        required
                                        autocomplete="username"
                                        placeholder="name@example.com"
                                    />
                                    <InputError :message="errors.email" />
                                </div>
                            </div>

                            <div v-if="mustVerifyEmail && !user.email_verified_at" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <p class="text-sm text-amber-800">
                                    Your email address is unverified.
                                    <Link
                                        :href="send()"
                                        as="button"
                                        class="font-medium underline hover:text-amber-900"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>

                                <div
                                    v-if="status === 'verification-link-sent'"
                                    class="mt-2 text-sm font-medium text-green-600"
                                >
                                    A new verification link has been sent to your email address.
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <Button
                                    :disabled="processing"
                                    data-test="update-profile-button"
                                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-6"
                                >
                                    Save Changes
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-show="recentlySuccessful"
                                        class="text-sm text-green-600 font-medium flex items-center gap-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Saved successfully.
                                    </p>
                                </Transition>
                            </div>
                        </Form>
                    </div>
                </div>

                <!-- Delete Account Section -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-red-100 shadow-sm">
                    <DeleteUser />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
