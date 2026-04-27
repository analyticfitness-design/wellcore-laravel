<script setup>
defineProps({
    rows: { type: Array, required: true },
});
</script>

<template>
    <div class="overflow-x-auto rounded-lg border border-wc-border">
        <table class="w-full min-w-[720px] border-collapse text-left">
            <thead>
                <tr class="border-b border-wc-border bg-wc-bg/40">
                    <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                        Tiempo
                    </th>
                    <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                        Diálogo
                    </th>
                    <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                        Visual
                    </th>
                    <th class="px-4 py-3 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                        Notas de edit
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, idx) in rows"
                    :key="idx"
                    class="border-b border-wc-border/60 last:border-b-0 align-top"
                >
                    <td class="px-4 py-4 font-mono text-sm tabular-nums text-wc-text">
                        {{ row.time }}
                    </td>
                    <td class="px-4 py-4 text-sm leading-relaxed text-wc-text">
                        {{ row.dialogue }}
                    </td>
                    <td class="px-4 py-4 text-sm leading-relaxed text-wc-text-secondary">
                        {{ row.visual }}
                    </td>
                    <td
                        class="px-4 py-4 font-editorial text-sm italic leading-relaxed"
                        style="color: #c8a769"
                    >
                        {{ row.edit_notes }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
