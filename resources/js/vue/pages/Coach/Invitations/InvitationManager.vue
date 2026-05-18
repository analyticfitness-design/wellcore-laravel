<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import CoachLayout from '../../../layouts/CoachLayout.vue';
import { useInvitationsStore } from '../../../stores/invitationsStore';
import InvitationList from './InvitationList.vue';
import InvitationForm from './InvitationForm.vue';

const { t } = useI18n();
const store = useInvitationsStore();

// 'list' | 'form'
const view = ref('list');

function showForm() {
    view.value = 'form';
}

function showList() {
    view.value = 'list';
}

function onFormSuccess() {
    showList();
    store.fetchInvitations();
}

onMounted(() => {
    store.fetchInvitations();
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ t('coach_growth.invitations.manager_title') }}</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            {{ t('coach_growth.invitations.manager_subtitle') }}
          </p>
        </div>

        <!-- Back button when in form mode -->
        <button
          v-if="view === 'form'"
          @click="showList"
          class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm font-medium text-wc-text hover:bg-zinc-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          {{ t('coach_growth.invitations.manager_back') }}
        </button>
      </div>

      <!-- View switcher -->
      <Transition name="fade" mode="out-in">
        <InvitationList
          v-if="view === 'list'"
          key="list"
          @new-invitation="showForm"
        />
        <div
          v-else
          key="form"
          class="rounded-[14px] border border-[var(--b1)] p-6 anim-entry anim-entry-2"
          style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
        >
          <InvitationForm
            @success="onFormSuccess"
            @cancel="showList"
          />
        </div>
      </Transition>

    </div>
  </CoachLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
