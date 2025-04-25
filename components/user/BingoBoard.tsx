import React, { useState, useEffect } from 'react';
import { api } from '@lib/api';
import BetSelector from './BetSelector';
import RoundSelector from './RoundSelector';
import PatternDisplay from './PatternDisplay';
import CardSelector from './CardSelector';

type BingoNumber = {
  number: number;
  called: boolean;
};

type BingoCard = {
  id: number;
  numbers: number[][];
  selected: boolean;
};

const BingoBoard: React.FC = () => {
  const [currentRound, setCurrentRound] = useState<number | null>(null);
  const [betAmount, setBetAmount] = useState<number>(0);
  const [pattern, setPattern] = useState<string | null>(null);
  const [commission, setCommission] = useState<number>(0);
  const [calledNumbers, setCalledNumbers] = useState<BingoNumber[]>([]);
  const [selectedCards, setSelectedCards] = useState<BingoCard[]>([]);
  const [isDemo, setIsDemo] = useState<boolean>(false);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    // Load initial game state
    const loadGameState = async () => {
      try {
        const response = await api.get('/api/user/gameboard');
        const { round, patterns, commission, availableCards } = response.data;
        
        setCurrentRound(round.id);
        setPattern(patterns[0]?.id || null);
        setCommission(commission);
        // Initialize other state as needed
        
        setLoading(false);
      } catch (error) {
        console.error('Failed to load game state', error);
        setLoading(false);
      }
    };
    
    loadGameState();
  }, []);

  const handleBetChange = (amount: number) => {
    setBetAmount(amount);
  };

  const handleRoundChange = (roundId: number) => {
    setCurrentRound(roundId);
    // Load round-specific data
  };

  const handlePatternChange = (patternId: string) => {
    setPattern(patternId);
  };

  const handleCardSelect = (card: BingoCard) => {
    setSelectedCards(prev => {
      const exists = prev.some(c => c.id === card.id);
      if (exists) {
        return prev.filter(c => c.id !== card.id);
      } else {
        return [...prev, card];
      }
    });
  };

  const handleRandomCards = async () => {
    try {
        const response = await api.post('/api/user/cards/random', {
            count: 4, // Number of random cards to select
            roundId: currentRound
          });
          
          setSelectedCards(response.data.cards);
        } catch (error) {
          console.error('Failed to get random cards', error);
        }
      };
    
      const handleClearCards = () => {
        setSelectedCards([]);
      };
    
      const toggleDemoMode = () => {
        setIsDemo(!isDemo);
        if (!isDemo) {
          // Generate random called numbers for demo mode
          const demoNumbers: BingoNumber[] = [];
          for (let i = 1; i <= 75; i++) {
            demoNumbers.push({
              number: i,
              called: Math.random() > 0.7 // Randomly mark some as called
            });
          }
          setCalledNumbers(demoNumbers);
        } else {
          // Reset to actual game state
          // TODO: Fetch real called numbers from API
        }
      };
    
      const submitBet = async () => {
        if (!currentRound || betAmount <= 0 || selectedCards.length === 0 || !pattern) {
          alert('Please complete all required fields');
          return;
        }
    
        try {
          await api.post('/api/user/bet', {
            roundId: currentRound,
            betAmount,
            patternId: pattern,
            cardIds: selectedCards.map(card => card.id)
          });
          
          alert('Bet placed successfully!');
          // Optionally refresh game state or redirect
        } catch (error) {
          console.error('Failed to place bet', error);
          alert('Failed to place bet. Please try again.');
        }
      };
    
      if (loading) {
        return <div className="flex items-center justify-center h-screen">Loading game board...</div>;
      }
    
      return (
        <div className="container p-4 mx-auto">
          <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
            {/* Left Column - Game Controls */}
            <div className="p-4 bg-white rounded-lg shadow-md">
              <h2 className="mb-4 text-xl font-bold">Game Controls</h2>
              
              <div className="mb-4">
                <BetSelector onBetChange={handleBetChange} value={betAmount} />
              </div>
              
              <div className="mb-4">
                <RoundSelector 
                  onRoundChange={handleRoundChange} 
                  selectedRound={currentRound} 
                />
              </div>
              
              <div className="mb-4">
                <PatternDisplay 
                  onPatternChange={handlePatternChange}
                  selectedPattern={pattern}
                  commission={commission}
                />
              </div>
              
              <div className="flex flex-col gap-2 mt-6">
                <button 
                  onClick={toggleDemoMode}
                  className={`px-4 py-2 font-bold text-white rounded ${isDemo ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-500 hover:bg-blue-600'}`}
                >
                  {isDemo ? 'Exit Demo Mode' : 'Enter Demo Mode'}
                </button>
                
                <button 
                  onClick={submitBet}
                  disabled={!currentRound || betAmount <= 0 || selectedCards.length === 0 || !pattern}
                  className="px-4 py-2 font-bold text-white bg-green-500 rounded hover:bg-green-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                  Place Bet
                </button>
              </div>
            </div>
            
            {/* Middle Column - Bingo Board */}
            <div className="p-4 bg-white rounded-lg shadow-md">
              <h2 className="mb-4 text-xl font-bold">Bingo Numbers</h2>
              
              <div className="grid grid-cols-5 gap-2">
                {/* B Column */}
                <div className="flex flex-col gap-2">
                  <div className="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-600 rounded-full">B</div>
                  {calledNumbers
                    .filter(n => n.number >= 1 && n.number <= 15)
                    .sort((a, b) => a.number - b.number)
                    .map(num => (
                      <div 
                        key={num.number}
                        className={`flex items-center justify-center w-10 h-10 rounded-full ${num.called ? 'bg-green-500 text-white' : 'bg-gray-200'}`}
                      >
                        {num.number}
                      </div>
                    ))
                  }
                </div>
                
                {/* I Column */}
                <div className="flex flex-col gap-2">
                  <div className="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-600 rounded-full">I</div>
                  {calledNumbers
                    .filter(n => n.number >= 16 && n.number <= 30)
                    .sort((a, b) => a.number - b.number)
                    .map(num => (
                      <div 
                        key={num.number}
                        className={`flex items-center justify-center w-10 h-10 rounded-full ${num.called ? 'bg-green-500 text-white' : 'bg-gray-200'}`}
                      >
                        {num.number}
                      </div>
                    ))
                  }
                </div>
                
                {/* N Column */}
                <div className="flex flex-col gap-2">
                  <div className="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-600 rounded-full">N</div>
                  {calledNumbers
                    .filter(n => n.number >= 31 && n.number <= 45)
                    .sort((a, b) => a.number - b.number)
                    .map(num => (
                      <div 
                        key={num.number}
                        className={`flex items-center justify-center w-10 h-10 rounded-full ${num.called ? 'bg-green-500 text-white' : 'bg-gray-200'}`}
                      >
                        {num.number}
                      </div>
                    ))
                  }
                </div>
                
                {/* G Column */}
                <div className="flex flex-col gap-2">
                  <div className="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-600 rounded-full">G</div>
                  {calledNumbers
                    .filter(n => n.number >= 46 && n.number <= 60)
                    .sort((a, b) => a.number - b.number)
                    .map(num => (
                      <div 
                        key={num.number}
                        className={`flex items-center justify-center w-10 h-10 rounded-full ${num.called ? 'bg-green-500 text-white' : 'bg-gray-200'}`}
                      >
                        {num.number}
                      </div>
                    ))
                  }
                </div>
                
                {/* O Column */}
                <div className="flex flex-col gap-2">
                  <div className="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-600 rounded-full">O</div>
                  {calledNumbers
                    .filter(n => n.number >= 61 && n.number <= 75)
                    .sort((a, b) => a.number - b.number)
                    .map(num => (
                      <div 
                        key={num.number}
                        className={`flex items-center justify-center w-10 h-10 rounded-full ${num.called ? 'bg-green-500 text-white' : 'bg-gray-200'}`}
                      >
                        {num.number}
                      </div>
                    ))
                  }
                </div>
              </div>
              
              <div className="mt-6">
                <h3 className="mb-2 text-lg font-semibold">Last Called:</h3>
                <div className="flex items-center justify-center w-16 h-16 text-2xl font-bold text-white bg-red-600 rounded-full">
                  {calledNumbers.find(n => n.called)?.number || '-'}
                </div>
              </div>
            </div>
            
            {/* Right Column - Card Selection */}
            <div className="p-4 bg-white rounded-lg shadow-md">
              <h2 className="mb-4 text-xl font-bold">Card Selection</h2>
              
              <div className="flex flex-col gap-4">
                <div className="flex gap-2">
                  <button 
                    onClick={handleRandomCards}
                    className="px-4 py-2 font-bold text-white bg-purple-500 rounded hover:bg-purple-600"
                  >
                    Random Cards
                  </button>
                  
                  <button 
                    onClick={handleClearCards}
                    className="px-4 py-2 font-bold text-white bg-red-500 rounded hover:bg-red-600"
                  >
                    Clear Cards
                  </button>
                </div>
                
                <div>
                  <p className="mb-2 font-semibold">Selected Cards: {selectedCards.length}</p>
                  <CardSelector 
                    onCardSelect={handleCardSelect} 
                    selectedCards={selectedCards}
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      );
    };
    
    export default BingoBoard;
    