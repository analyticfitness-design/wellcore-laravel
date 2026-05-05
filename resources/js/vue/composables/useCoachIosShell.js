import { ref } from 'vue';

const _actionSheetOpen = ref(false);
const _cmdPaletteOpen = ref(false);
const _sidebarOpen = ref(false);

/**
 * useCoachIosShell — singleton state for the Coach iOS layer.
 * Manages action sheet (mobile), Cmd+K palette (desktop), sidebar drawer (mobile).
 * Returned refs are shared across all consumers so any component can open/close.
 */
export function useCoachIosShell() {
    return {
        actionSheetOpen: _actionSheetOpen,
        cmdPaletteOpen: _cmdPaletteOpen,
        sidebarOpen: _sidebarOpen,
        openActionSheet: () => { _actionSheetOpen.value = true; },
        closeActionSheet: () => { _actionSheetOpen.value = false; },
        openCmdPalette: () => { _cmdPaletteOpen.value = true; },
        closeCmdPalette: () => { _cmdPaletteOpen.value = false; },
        openSidebar: () => { _sidebarOpen.value = true; },
        closeSidebar: () => { _sidebarOpen.value = false; },
        toggleSidebar: () => { _sidebarOpen.value = !_sidebarOpen.value; },
        toggleDark: () => {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            try {
                localStorage.setItem('darkMode', String(isDark));
            } catch (_) {}
            return isDark;
        },
    };
}
