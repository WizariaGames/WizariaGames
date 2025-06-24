document.addEventListener('mousemove', (e) => {
  const blob = document.getElementById('blob');
  blob.style.transform = `translate(${e.clientX - 150}px, ${e.clientY - 150}px)`;
});
