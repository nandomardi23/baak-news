/**
 * Composable for status badge styling.
 * Consolidates getBadgeClass and getStatusBadge functions
 * previously duplicated across 7+ files.
 */
export function useStatusBadge() {
    /**
     * Get CSS classes for document/surat status badges.
     * Used in: Surat/Index, Surat/Show, Dashboard, Dokumen, Status
     */
    const getBadgeClass = (badge: string): string => {
        const classes: Record<string, string> = {
            // Badge-type keys (from backend status_badge)
            warning: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
            success: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
            danger: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            secondary: 'bg-slate-50 text-slate-600 border-slate-200',
            // Status keys (used in Surat/Show)
            pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
            processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
            rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            printed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        };
        return classes[badge] || 'bg-gray-100 text-gray-800';
    };

    /**
     * Get CSS classes for mahasiswa/dosen status badges.
     * Used in: Mahasiswa/Index, Dosen/Index
     */
    const getStatusBadge = (status: string | null): string => {
        const statusMap: Record<string, string> = {
            Aktif: 'bg-emerald-100 text-emerald-700',
            'Non-Aktif': 'bg-red-100 text-red-700',
            Lulus: 'bg-blue-100 text-blue-700',
            'Do': 'bg-red-100 text-red-700',
            Cuti: 'bg-amber-100 text-amber-700',
            Keluar: 'bg-slate-100 text-slate-600',
            'Sedang Studi': 'bg-emerald-100 text-emerald-700',
        };
        return statusMap[status || ''] || 'bg-slate-100 text-slate-600';
    };

    return { getBadgeClass, getStatusBadge };
}
