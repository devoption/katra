import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

console.log('🔧 Echo config:', {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'wss' || (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: [(import.meta.env.VITE_REVERB_SCHEME === 'ws' ? 'ws' : 'wss')],
});

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'wss' || (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: [(import.meta.env.VITE_REVERB_SCHEME === 'ws' ? 'ws' : 'wss')],
    });
    console.log('✅ Echo initialized successfully');
    console.log('🔌 Echo connection state after init:', window.Echo.connector.pusher.connection.state);
} catch (error) {
    console.error('❌ Failed to initialize Echo:', error);
}
