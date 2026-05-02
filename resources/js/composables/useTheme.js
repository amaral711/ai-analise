import { ref } from 'vue'

const STORAGE_KEY = 'deepscan-theme'

const isDark = ref(
    typeof window !== 'undefined'
        ? (localStorage.getItem(STORAGE_KEY) ?? 'dark') === 'dark'
        : true
)

function apply(dark) {
    document.documentElement.classList.toggle('dark', dark)
}

export function useTheme() {
    function toggle() {
        isDark.value = !isDark.value
        apply(isDark.value)
        localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light')
    }

    return { isDark, toggle }
}
