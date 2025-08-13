import { useDark } from '@vueuse/core'

export function useTheme() {
    // Estado reactivo para modo oscuro
    const isDark = useDark()

    // Alternar entre dark y light
    const toggleTheme = () => {
        isDark.value = !isDark.value
    }

    return {
        isDark,
        toggleTheme,
    }
}