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
                        class="group/item transition-all duration-200 ease-in-out hover:bg-slate-100/80 hover:text-slate-900 data-[active=true]:bg-blue-50 data-[active=true]:text-blue-700 data-[active=true]:font-medium data-[active=true]:shadow-sm rounded-lg py-2.5 px-3"
                    >
                        <Link :href="item.href" class="flex items-center gap-3">
                            <component 
                                :is="item.icon" 
                                class="w-[18px] h-[18px] transition-colors duration-200 group-data-[active=true]/item:text-blue-600 text-slate-500 group-hover/item:text-slate-700" 
                            />
                            <span>{{ item.title }}</span>
                            <div 
                                v-if="urlIsActive(item.href)"
                                class="w-1.5 h-1.5 rounded-full bg-blue-600 ml-auto shadow-[0_0_8px_rgba(37,99,235,0.5)]"
                            ></div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </template>
</template>
