// c:\xampp\htdocs\wizaria 2\interactive-background.js
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('interactive-background-container');
    // Se o container não existir na página atual, não faz nada
    if (!container) {
        return;
    }

    const elements = container.querySelectorAll('.interactive-bg-element');
    const repulsionRadius = 150; // Raio de influência do mouse (em pixels)
    const repulsionStrength = 1500; // Aumentado para maior "lançamento"

    // As imagens usarão 'top' e 'left' do CSS para sua posição base.
    // O 'transform' será usado apenas para o efeito de repulsão.
    elements.forEach(el => {
        el.style.transform = 'translate(0px, 0px)'; // Garante estado inicial do transform
    });


    document.addEventListener('mousemove', (e) => {
        const mouseX = e.clientX;
        const mouseY = e.clientY;

        elements.forEach(el => {
            const elRect = el.getBoundingClientRect();
            // Calcula o centro do elemento na tela
            const elCenterX = elRect.left + elRect.width / 2;
            const elCenterY = elRect.top + elRect.height / 2;

            // Vetor do centro do elemento para o mouse
            const deltaX = mouseX - elCenterX;
            const deltaY = mouseY - elCenterY;

            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

            let targetTranslateX = 0;
            let targetTranslateY = 0;

            if (distance < repulsionRadius && distance !== 0) {
                // Calcula a força da repulsão (mais forte quando mais perto)
                const forceFactor = (repulsionRadius - distance) / repulsionRadius;

                // Direção de repulsão (oposta ao vetor deltaX, deltaY, normalizado)
                targetTranslateX = -(deltaX / distance) * repulsionStrength * forceFactor;
                targetTranslateY = -(deltaY / distance) * repulsionStrength * forceFactor;
            }
            // Se o mouse estiver longe, targetTranslateX/Y permanecem 0, voltando à posição original.

            el.style.transform = `translate(${targetTranslateX}px, ${targetTranslateY}px)`;

        });
    });


});
