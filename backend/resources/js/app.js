import './bootstrap';

// Import any additional libraries you need
import Alpine from 'alpinejs';
import axios from 'axios';

// Make Alpine available globally
window.Alpine = Alpine;
Alpine.start();

// Bingo Card Component
document.addEventListener('DOMContentLoaded', () => {
    // Handle bingo card selection
    const cardSelectors = document.querySelectorAll('.card-selector');
    if (cardSelectors) {
        cardSelectors.forEach(selector => {
            selector.addEventListener('click', function() {
                this.classList.toggle('selected');
                const cardId = this.dataset.cardId;
                const selectedCards = document.getElementById('selected-cards');
                
                if (this.classList.contains('selected')) {
                    if (!selectedCards.value.includes(cardId)) {
                        selectedCards.value += (selectedCards.value ? ',' : '') + cardId;
                    }
                } else {
                    selectedCards.value = selectedCards.value
                        .split(',')
                        .filter(id => id !== cardId)
                        .join(',');
                }
            });
        });
    }
    
    // Live game updates
    const gameBoard = document.getElementById('game-board');
    if (gameBoard) {
        const roundId = gameBoard.dataset.roundId;
        const calledNumbersElement = document.getElementById('called-numbers');
        
        // Poll for updates every 5 seconds
        setInterval(() => {
            axios.get(`/api/user/rounds/${roundId}/called-numbers`)
                .then(response => {
                    const calledNumbers = response.data.called_numbers;
                    calledNumbersElement.innerHTML = '';
                    
                    calledNumbers.forEach(number => {
                        const numberElement = document.createElement('div');
                        numberElement.classList.add('called-number');
                        numberElement.textContent = number;
                        calledNumbersElement.appendChild(numberElement);
                        
                        // Highlight numbers on cards
                        document.querySelectorAll(`.card-number-${number}`).forEach(cell => {
                            cell.classList.add('marked');
                        });
                    });
                    
                    // Check if round has ended
                    if (response.data.status === 'completed') {
                        document.getElementById('round-status').textContent = 'Round Completed';
                        document.getElementById('claim-win-btn').style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching game updates:', error));
        }, 5000);
        
        // Claim win functionality
        const claimWinBtn = document.getElementById('claim-win-btn');
        if (claimWinBtn) {
            claimWinBtn.addEventListener('click', () => {
                const betId = claimWinBtn.dataset.betId;
                axios.post(`/api/user/bets/${betId}/claim-win`)
                    .then(response => {
                        if (response.data.success) {
                            alert('Congratulations! Your win has been verified.');
                        } else {
                            alert('Sorry, your card does not match the winning pattern.');
                        }
                    })
                    .catch(error => {
                        console.error('Error claiming win:', error);
                        alert('There was an error processing your claim.');
                    });
            });
        }
    }
});
