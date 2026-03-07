import { ref } from 'vue';
import type { BreadcrumbItemType } from '@/types';

const breadcrumbs = ref<BreadcrumbItemType[]>([]);

export function useBreadcrumbs() {
    const setBreadcrumbs = (items: BreadcrumbItemType[]) => {
        breadcrumbs.value = items;
    };

    return {
        breadcrumbs,
        setBreadcrumbs,
    };
}
