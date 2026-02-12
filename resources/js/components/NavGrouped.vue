<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useActiveUrl } from '@/composables/useActiveUrl';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { type Component } from 'vue';

interface NavGroup {
    label: string;
    items: NavItem[];
}

defineProps<{
    groups: NavGroup[];
}>();

const { urlIsActive } = useActiveUrl();
</script>

<template>
    <template v-for="group in groups" :key="group.label">
        <SidebarGroup class="px-3 py-1.5">
            <SidebarGroupLabel class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2 mb-2">
                {{ group.label }}
            </SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in group.items" :key="item.title">
                    <SidebarMenuButton
                        as-child
                        :is-active="urlIsActive(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href" class="flex items-center gap-3">
                            <component 
                                :is="item.icon" 
                                class="w-[18px] h-[18px] transition-colors duration-200 group-data-[active=true]/item:text-blue-600 text-slate-500 group-hover/item:text-slate-700" 
                            />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </template>
</template>
