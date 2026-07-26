/**
 * Alpine.js компоненты для приложения
 */

// Проверяем что Alpine доступен глобально
if (window.Alpine) {
    // Компонент для ленты событий матча
    window.Alpine.data('matchFeed', () => ({
        matchId: null,
        events: [],
        isLoading: false,
        autoScroll: true,
        subscription: null,

        init() {
            this.loadEvents();

            // Подписка на WebSocket если есть ID матча
            if (this.matchId) {
                this.subscribeToMatch();
            }
        },

        async loadEvents() {
            if (!this.matchId) return;

            this.isLoading = true;
            try {
                const response = await fetch(`/api/matches/${this.matchId}/events`);
                const data = await response.json();
                this.events = data.events || [];
            } catch (error) {
                console.error('Failed to load events:', error);
            } finally {
                this.isLoading = false;
            }
        },

        subscribeToMatch() {
            if (!this.matchId) return;

            this.subscription = window.subscribeToMatch(this.matchId, {
                onEvent: (data) => {
                    this.addEvent(data);
                },
                onFinished: () => {
                    this.$dispatch('match-finished');
                },
            });
        },

        addEvent(event) {
            this.events.push(event);

            // Автоскролл
            if (this.autoScroll) {
                this.$nextTick(() => {
                    const container = this.$refs.eventsContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            }
        },

        toggleAutoScroll() {
            this.autoScroll = !this.autoScroll;
        },

        destroy() {
            if (this.subscription) {
                window.unsubscribeFromMatch(this.matchId);
            }
        },
    }));

    // Компонент для статуса соединения
    window.Alpine.data('connectionStatus', () => ({
        status: 'disconnected',
        statusClass: 'bg-red-500',
        checkInterval: null,

        init() {
            this.updateStatus();
            this.checkInterval = setInterval(() => {
                this.updateStatus();
            }, 5000);
        },

        updateStatus() {
            const connection = window.checkConnection();
            this.status = connection.state || 'disconnected';

            switch (this.status) {
                case 'connected':
                    this.statusClass = 'bg-green-500';
                    break;
                case 'connecting':
                    this.statusClass = 'bg-yellow-500';
                    break;
                default:
                    this.statusClass = 'bg-red-500';
            }
        },

        destroy() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
        },
    }));

    console.log('✅ Alpine components registered');
} else {
    console.warn('⚠️ Alpine not available');
}

// Экспорт не нужен, так как компоненты регистрируются глобально
