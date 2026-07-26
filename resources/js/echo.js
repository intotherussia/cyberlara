import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

if (window.Echo && window.Echo.connector) {
    const connection = window.Echo.connector.pusher.connection;

    connection.bind('connected', () => {
        console.log('✅ WebSocket connected!');
    });

    connection.bind('disconnected', () => {
        console.log('❌ WebSocket disconnected');
    });

    connection.bind('error', (error) => {
        console.error('⚠️ WebSocket error:', error);
    });
}
