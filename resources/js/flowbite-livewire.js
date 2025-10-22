document.addEventListener('livewire:navigated', () => {
    if (window.Flowbite && typeof window.Flowbite.initPopovers === 'function') {
        window.Flowbite.initPopovers();
    }
});

document.addEventListener('livewire:update', () => {
    if (window.Flowbite && typeof window.Flowbite.initPopovers === 'function') {
        window.Flowbite.initPopovers();
    }
});
