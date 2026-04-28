/**
 * useAdminModules — fuente unica de verdad de los 24 modulos del admin v2.
 *
 * Cada modulo tiene:
 *  - id: identificador estable (slug)
 *  - name: label visible en sidebar y tools grid
 *  - to: ruta vue-router
 *  - routeName: nombre de la ruta (para active state)
 *  - icon: keyword de SVG (resuelto en AdminSidebar/AdminToolsGrid)
 *  - group: grupo del sidebar (general, financiero, equipo, marketing, planes,
 *           rise, comunicacion, growth, sistema)
 *  - badge?: texto opcional ("NUEVO") mostrado al lado del item
 *  - color?: variant para el ToolsGrid (rojo, amber, verde, azul, neutral)
 *  - permission?: clave de permiso opcional para filtrar por rol
 */

const MODULES = [
    // GENERAL
    { id: 'dashboard',      name: 'Dashboard',           to: '/admin',                 routeName: 'admin-dashboard',         icon: 'home',     group: 'general',     color: 'red'    },
    { id: 'feed',           name: 'Live Feed',           to: '/admin/feed',            routeName: 'admin-feed',              icon: 'feed',     group: 'general',     color: 'blue'   },
    { id: 'clientes',       name: 'Clientes',            to: '/admin/clients',         routeName: 'admin-clients',           icon: 'users',    group: 'general',     color: 'blue'   },
    { id: 'formularios',    name: 'Formularios',         to: '/admin/formularios',     routeName: 'admin-forms',             icon: 'form',     group: 'general',     color: 'neutral'},

    // FINANCIERO
    { id: 'pagos',          name: 'Pagos',               to: '/admin/payments',        routeName: 'admin-payments',          icon: 'card',     group: 'financiero',  color: 'green'  },
    { id: 'inscripciones',  name: 'Inscripciones',       to: '/admin/inscriptions',    routeName: 'admin-inscriptions',      icon: 'user-plus',group: 'financiero',  color: 'amber'  },
    { id: 'invitaciones',   name: 'Invitaciones',        to: '/admin/invitations',     routeName: 'admin-invitations',       icon: 'mail',     group: 'financiero',  color: 'neutral'},
    { id: 'comprobantes',   name: 'Comprobantes',        to: '/admin/payment-proofs',  routeName: 'admin-payment-proofs',    icon: 'check',    group: 'financiero',  color: 'neutral'},

    // EQUIPO
    { id: 'coaches',        name: 'Coaches',             to: '/admin/coaches',         routeName: 'admin-coaches',           icon: 'headset',  group: 'equipo',      color: 'red'    },

    // MARKETING
    { id: 'queue',          name: 'Cola de Drops',       to: '/admin/marketing/queue', routeName: 'admin-marketing-queue',   icon: 'megaphone',group: 'marketing',   color: 'amber',  badge: 'NUEVO' },

    // PLANES
    { id: 'planes',         name: 'Planes',              to: '/admin/plans',           routeName: 'admin-plans',             icon: 'clipboard',group: 'planes',      color: 'blue'   },
    { id: 'ai-generator',   name: 'Generador IA',        to: '/admin/ai-generator',    routeName: 'admin-ai-generator',      icon: 'sparkles', group: 'planes',      color: 'amber'  },

    // RISE
    { id: 'rise',           name: 'RISE',                to: '/admin/rise',            routeName: 'admin-rise',              icon: 'lightning',group: 'rise',        color: 'red'    },

    // COMUNICACION
    { id: 'chat-analytics', name: 'Chat Analytics',      to: '/admin/chat-analytics',  routeName: 'admin-chat-analytics',    icon: 'chart',    group: 'comunicacion',color: 'blue'   },
    { id: 'tickets',        name: 'Tickets',             to: '/admin/tickets',         routeName: 'admin-tickets',           icon: 'ticket',   group: 'comunicacion',color: 'amber'  },
    { id: 'plan-tickets',   name: 'Tickets de Planes',   to: '/admin/plan-tickets',    routeName: 'admin-plan-tickets',      icon: 'ticket-2', group: 'comunicacion',color: 'red'    },
    { id: 'client-requests',name: 'Solicitudes Coaches', to: '/admin/client-requests', routeName: 'admin-client-requests',   icon: 'inbox',    group: 'comunicacion',color: 'blue'   },
    { id: 'plan-tickets-stats', name: 'Stats de Tickets',to: '/admin/plan-tickets/stats', routeName: 'admin-plan-tickets-stats', icon: 'stats', group: 'comunicacion',color: 'neutral'},

    // GROWTH
    { id: 'campanas',       name: 'Campanas',            to: '/admin/campaigns',       routeName: 'admin-campaigns',         icon: 'target',   group: 'growth',      color: 'amber'  },
    { id: 'referidos',      name: 'Referidos',           to: '/admin/referrals',       routeName: 'admin-referrals',         icon: 'share',    group: 'growth',      color: 'green'  },

    // SISTEMA
    { id: 'tools',          name: 'Herramientas',        to: '/admin/tools',           routeName: 'admin-tools',             icon: 'wrench',   group: 'sistema',     color: 'neutral'},
    { id: 'audit-log',      name: 'Audit Log',           to: '/admin/audit-log',       routeName: 'admin-audit-log',         icon: 'shield',   group: 'sistema',     color: 'neutral'},
    { id: 'settings',       name: 'Configuracion',       to: '/admin/settings',        routeName: 'admin-settings',          icon: 'settings', group: 'sistema',     color: 'neutral'},
];

// Orden canonico de grupos para el sidebar
const GROUP_ORDER = [
    { id: 'general',      label: 'GENERAL'      },
    { id: 'financiero',   label: 'FINANCIERO'   },
    { id: 'equipo',       label: 'EQUIPO'       },
    { id: 'marketing',    label: 'MARKETING'    },
    { id: 'planes',       label: 'PLANES'       },
    { id: 'rise',         label: 'RISE'         },
    { id: 'comunicacion', label: 'COMUNICACION' },
    { id: 'growth',       label: 'GROWTH'       },
    { id: 'sistema',      label: 'SISTEMA'      },
];

/**
 * Devuelve los modulos agrupados por seccion en el orden canonico, cada grupo
 * con label legible y array de items.
 */
export function useAdminModules() {
    const grouped = GROUP_ORDER.map(g => ({
        id: g.id,
        label: g.label,
        items: MODULES.filter(m => m.group === g.id),
    })).filter(g => g.items.length > 0);

    return {
        modules: MODULES,
        groupedModules: grouped,
    };
}

// Atajos rapidos del bottom nav mobile (5 fixed tabs)
export const BOTTOM_NAV_ROUTES = ['admin-dashboard', 'admin-clients', 'admin-payments', 'admin-feed'];
