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
        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                {{ group.label }}
            </SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in group.items" :key="item.title">
                    <SidebarMenuButton
                        as-child
                        :is-active="urlIsActive(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </template>
</template>
