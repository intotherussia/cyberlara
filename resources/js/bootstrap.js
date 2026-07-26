// Import necessary modules
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Set up Echo for WebSocket functionality
window.Pusher = Pusher;

// Create a global Echo instance
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Function to subscribe to match channels
window.subscribeToMatch = (matchId, callbacks) => {
    const channel = window.Echo.channel(`match.${matchId}`);
    
    channel.listen('match.event', (data) => {
        if (callbacks.onEvent) callbacks.onEvent(data);
    });
    
    channel.listen('match.finished', (data) => {
        if (callbacks.onFinished) callbacks.onFinished(data);
    });
    
    return channel;
};