<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    rows: { type: Array, required: true },
});
</script>

<template>
    <div class="timecode-scroll">
        <table class="timecode-table">
            <thead>
                <tr>
                    <th>{{ t('coach_growth.strategy.tc_time') }}</th>
                    <th>{{ t('coach_growth.strategy.tc_dialogue') }}</th>
                    <th>{{ t('coach_growth.strategy.tc_visual') }}</th>
                    <th>{{ t('coach_growth.strategy.tc_edit_notes') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, idx) in rows" :key="idx">
                    <td class="col-t">{{ row.time }}</td>
                    <td class="col-d">{{ row.dialogue }}</td>
                    <td class="col-v">{{ row.visual }}</td>
                    <td class="col-e">{{ row.edit_notes }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
