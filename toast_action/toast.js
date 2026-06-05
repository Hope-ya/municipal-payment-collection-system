function showToast(message, type = 'success', reloadAfterSuccess = true) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Create toast div
    const toast = document.createElement('div');
    toast.className = `p-4 mb-2 rounded-lg shadow-lg text-white transform transition-all duration-500 ease-in-out opacity-0 -translate-x-5 
        ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    toast.style.whiteSpace = 'pre-line'; // allow line breaks
    toast.textContent = message;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', '-translate-x-5');
        toast.classList.add('opacity-100', 'translate-x-0');
    });

    // Determine how long the toast should stay
    const duration = type === 'success' ? 3000 : 5000; // error stays longer

    // Optionally reload after success 
    if (type === 'success' && reloadAfterSuccess) {
        setTimeout(() => location.reload(), 1000);
    }

    // Animate out and remove toast
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-x-0');
        toast.classList.add('opacity-0', '-translate-x-5');
        setTimeout(() => toast.remove(), 500);
    }, duration);
}
