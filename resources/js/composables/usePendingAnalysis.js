import { ref } from 'vue'

// In-memory only — resets on page refresh (state is fetched from server on widget mount)
const analyses = ref([])

export function usePendingAnalysis() {
    function setFromServer(serverList) {
        const merged = serverList.map(item => {
            const existing = analyses.value.find(a => a.id === item.id && a.type === item.type)
            // Preserve completed/failed status already received via Echo
            if (existing && existing.status !== 'analyzing') return existing
            return { id: item.id, type: item.type, label: item.label ?? '', status: 'analyzing' }
        })
        // Keep completed/failed items still shown in the widget (not yet dismissed)
        const finished = analyses.value.filter(a =>
            (a.status === 'completed' || a.status === 'failed') &&
            !serverList.find(s => s.id === a.id && s.type === a.type)
        )
        analyses.value = [...merged, ...finished]
    }

    function markCompleted(id, type) {
        const exists = analyses.value.find(a => a.id === id && a.type === type)
        if (exists) {
            analyses.value = analyses.value.map(a =>
                a.id === id && a.type === type ? { ...a, status: 'completed' } : a
            )
        } else {
            // Analysis completed but wasn't in local state (e.g. submitted from another tab)
            analyses.value = [...analyses.value, { id, type, label: '', status: 'completed' }]
        }
    }

    function markFailed(id, type) {
        const exists = analyses.value.find(a => a.id === id && a.type === type)
        if (exists) {
            analyses.value = analyses.value.map(a =>
                a.id === id && a.type === type ? { ...a, status: 'failed' } : a
            )
        } else {
            analyses.value = [...analyses.value, { id, type, label: '', status: 'failed' }]
        }
    }

    function removePending(id, type) {
        analyses.value = analyses.value.filter(a => !(a.id === id && a.type === type))
    }

    return { analyses, setFromServer, markCompleted, markFailed, removePending }
}
