/**
 * Основной файл приложения
 */

import './bootstrap';

// Проверяем, что Livewire еще не загружен
if (!window.Livewire) {
    import('../../vendor/livewire/livewire/dist/livewire.esm').then(({ Livewire }) => {
        window.Livewire = Livewire;
        Livewire.start();
        console.log('✅ Livewire initialized from app.js');
    });
} else {
    console.log('✅ Livewire already loaded');
}

// Echo
import './echo';

console.log('✅ Application initialized');
console.log('📡 Echo available:', !!window.Echo);
console.log('📡 Livewire available:', !!window.Livewire);
