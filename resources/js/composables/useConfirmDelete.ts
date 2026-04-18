import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

export interface ConfirmDeleteOptions {
    /** Target URL to send DELETE request */
    url: string;
    
    /** Name of the entity being deleted (e.g., 'Dosen', 'Mata Kuliah') */
    entityName?: string; 

    /** Default body text if not restricted. If not provided, a default template is used. */
    text?: string; 

    /** If true (or a truthy value), blocks deletion and shows an error dialog */
    isRestricted?: boolean | number | string | null; 
    
    /** Text to show if restricted. Mandatory if isRestricted might be true for meaningful UI. */
    restrictedMessage?: string; 
    
    /** Custom callback to execute upon successful router.delete */
    onSuccess?: () => void;
    
    /** Custom callback if execution is aborted or fails */
    onError?: () => void;
}

export function useConfirmDelete() {
    const confirmDelete = (options: ConfirmDeleteOptions) => {
        // Skema data diblokir/dibatasi (Restricted)
        if (options.isRestricted) {
            Swal.fire({
                title: 'Tidak Dapat Dihapus!',
                text: options.restrictedMessage || `Data tidak dapat dihapus karena berelasi dengan data lain.`,
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Tutup',
            });
            return;
        }

        // Skema Normal (Warning Deletion)
        const entityLabel = options.entityName ? ` ${options.entityName}` : '';
        const defaultWarning = `Tindakan ini tidak dapat dibatalkan. Data${entityLabel} akan dihapus permanen.`;

        Swal.fire({
            title: 'Apakah anda yakin?',
            text: options.text || defaultWarning,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(options.url, {
                    onSuccess: () => {
                        toast.success('Berhasil', { description: `Data${entityLabel} berhasil dihapus` });
                        if (options.onSuccess) options.onSuccess();
                    },
                    onError: () => {
                        if (options.onError) options.onError();
                    }
                });
            }
        });
    };

    return { confirmDelete };
}
