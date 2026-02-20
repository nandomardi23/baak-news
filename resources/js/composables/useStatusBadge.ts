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
            warning: 'bg-amber-50 text-amber-700 border-amber-200',
            success: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            danger: 'bg-red-50 text-red-700 border-red-200',
            info: 'bg-blue-50 text-blue-700 border-blue-200',
            secondary: 'bg-slate-50 text-slate-600 border-slate-200',
        };
        return classes[badge] || classes.secondary;
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
