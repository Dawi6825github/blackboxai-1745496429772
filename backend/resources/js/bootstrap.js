import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Listen for bingo number calls
window.Echo.channel('bingo-game')
    .listen('NumberCalled', (e) => {
        console.log('New number called:', e.number);
        
        // Update UI with the new called number
        const calledNumbersElement = document.getElementById('called-numbers');
        if (calledNumbersElement) {
            const numberElement = document.createElement('div');
            numberElement.classList.add('called-number');
            numberElement.textContent = e.number;
            calledNumbersElement.appendChild(numberElement);
            
            // Highlight the number on all cards
            document.querySelectorAll(`.card-number-${e.number}`).forEach(cell => {
                cell.classList.add('marked');
            });
        }
    })
    .listen('RoundEnded', (e) => {
        console.log('Round ended:', e.roundId);
        
        // Update UI to show round has ended
        const roundStatusElement = document.getElementById('round-status');
        if (roundStatusElement) {
            roundStatusElement.textContent = 'Round Completed';
        }
        
        // Hide claim win button
        const claimWinBtn = document.getElementById('claim-win-btn');
        if (claimWinBtn) {
            claimWinBtn.style.display = 'none';
        }
        
        // Show results if available
        if (e.winners && e.winners.length > 0) {
            const resultsElement = document.getElementById('round-results');
            if (resultsElement) {
                resultsElement.innerHTML = '<h3>Winners</h3>';
                e.winners.forEach(winner => {
                    resultsElement.innerHTML += `<p>${winner.user_name} - Card #${winner.card_id}</p>`;
                });
                resultsElement.style.display = 'block';
            }
        }
    })
    .listen('RoundStarted', (e) => {
        console.log('Round started:', e.roundId);
        
        // Update UI to show round has started
        const roundStatusElement = document.getElementById('round-status');
        if (roundStatusElement) {
            roundStatusElement.textContent = 'Round In Progress';
        }
        
        // Show appropriate buttons
        const claimWinBtn = document.getElementById('claim-win-btn');
        if (claimWinBtn) {
            claimWinBtn.style.display = 'block';
        }
        
        // Reset called numbers display
        const calledNumbersElement = document.getElementById('called-numbers');
        if (calledNumbersElement) {
            calledNumbersElement.innerHTML = '';
        }
    });
