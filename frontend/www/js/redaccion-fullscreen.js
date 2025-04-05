document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.querySelector('textarea');
    if (!textarea) return;
  
    const resizeEditor = () => {
      const offsetTop = textarea.getBoundingClientRect().top;
      const availableHeight = window.innerHeight - offsetTop - 40; // 40px buffer
      textarea.style.height = `${availableHeight}px`;
    };
  
    // Initial resize
    resizeEditor();
  
    // Adjust on window resize
    window.addEventListener('resize', resizeEditor);
  });
  