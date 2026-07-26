<?php

// Import necessary modules
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Set up Pusher as the WebSocket client
window.Pusher = Pusher;

// Create a new Echo instance with Reverb configuration
const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Export the Echo instance for use in other files
export default echo;