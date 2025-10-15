import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Enable Pusher logging for debugging
Pusher.logToConsole = true;

// Check WebSocket support
console.log('🌐 WebSocket support:', typeof WebSocket !== 'undefined' ? 'YES' : 'NO');
console.log('🌐 WebSocket:', WebSocket);

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'wss';
const reverbPort = parseInt(import.meta.env.VITE_REVERB_PORT) || 8080;
const useTLS = reverbScheme === 'wss';

console.log('🔧 Parsed config:', {
    scheme: reverbScheme,
    port: reverbPort,
    useTLS: useTLS,
    host: import.meta.env.VITE_REVERB_HOST,
    key: import.meta.env.VITE_REVERB_APP_KEY,
});

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: useTLS,
        enabledTransports: ['wss', 'ws'], // Allow both and let Pusher choose
    });
    console.log('✅ Echo initialized successfully');
    console.log('🔌 Echo connection state after init:', window.Echo.connector.pusher.connection.state);
    
    // Force connection if not already connecting
    if (window.Echo.connector.pusher.connection.state === 'initialized' || 
        window.Echo.connector.pusher.connection.state === 'failed') {
        console.log('🔄 Forcing connection to Reverb...');
        window.Echo.connector.pusher.connect();
    }
} catch (error) {
    console.error('❌ Failed to initialize Echo:', error);
}
