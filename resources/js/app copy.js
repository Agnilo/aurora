import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {

    const tiles = document.querySelectorAll('.category-link');
    const goalsContent = document.getElementById('goalsDynamic');
    const loader = document.getElementById('goalsContentLoader');
    const grid = document.querySelector('.category-grid');

    let lastCategory = null;

    if (!tiles.length || !goalsContent || !loader) return;

    tiles.forEach(tile => {
        tile.addEventListener('click', () => {

            const categoryId = tile.dataset.category;
            const url = categoryId
                ? `${window.location.origin}${window.location.pathname}?category=${categoryId}&ajax=1`
                : `${window.location.origin}${window.location.pathname}?ajax=1`;

            const isReset = tile.classList.contains('active');

            if (isReset) {

                const activeTile = document.querySelector('.category-link.active');

                // 1) Snapshot (lock width/flex)
                tiles.forEach(t => {
                    const rect = t.getBoundingClientRect();
                    t.style.width = rect.width + "px";
                    t.style.flex = "none";
                });

                // 2) Next frame: switch layout (flex → grid)
                requestAnimationFrame(() => {

                    grid.classList.remove('has-active'); // flex off
                    grid.classList.add('closing');       // closing anim

                    // 3) Next-next frame: release width/flex instantly
                    requestAnimationFrame(() => {
                        tiles.forEach(t => {
                            t.style.width = "";
                            t.style.flex = "";
                        });
                    });
                });

                // 4) Continue reset sequence (styles, outlines, etc)
                setTimeout(() => {

                    if (activeTile) activeTile.classList.remove('active');
                    grid.classList.add('resetting');
                    grid.classList.remove('closing');

                }, 300);

                setTimeout(() => {
                    grid.classList.remove('resetting');
                }, 600);
            } else {
                tiles.forEach(t => t.classList.remove('active'));
                tile.classList.add('active');
                grid.classList.add('has-active');
            }

            // SLIDE direction
            window.slideDirection =
                lastCategory === null || isReset
                    ? "left"
                    : (categoryId > lastCategory ? "left" : "right");

            lastCategory = categoryId;

            // Show skeleton immediately
            showLoader();

            // Fade out dynamic content
            fadeOutAndHide(goalsContent, () => {

                minimumDelay(
                    fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" }}).then(r => r.text()),
                    500
                )
                .then(html => {
                    hideLoader();
                    fadeInWithContent(goalsContent, html);
                });
            });
        });
    });
});


//
// ============ ANIMATION HELPERS ===============
//

function fadeOutAndHide(element, callback) {
    element.style.transition = "opacity 0.45s ease, filter 0.45s ease";
    element.style.opacity = 0;
    element.style.filter = "blur(8px)";

    setTimeout(() => {
        element.style.display = "none";
        callback();
    }, 450);
}

function fadeInWithContent(container, newHtml) {

    container.style.display = "block";
    container.innerHTML = newHtml;

    // Slide animation base
    container.style.transform =
        window.slideDirection === "left"
            ? "translateX(30px)"
            : "translateX(-30px)";

    container.style.opacity = 0;
    container.style.filter = "blur(8px)";
    container.style.transition =
        "opacity 0.45s ease, filter 0.45s ease, transform 0.45s ease";

    setTimeout(() => {
        container.style.opacity = 1;
        container.style.filter = "blur(0px)";
        container.style.transform = "translateX(0px)";
    }, 30);

    // STAGGER list items
    setTimeout(() => {
        const cards = container.querySelectorAll('.goal-card');
        cards.forEach((card, i) => {
            setTimeout(() => {
                card.classList.add('stagger-in');
            }, i * 70);
        });
    }, 200);
}

function minimumDelay(promise, ms) {
    return Promise.all([
        promise,
        new Promise(resolve => setTimeout(resolve, ms))
    ]).then(([result]) => result);
}

function showLoader() {
    document.getElementById('goalsContentLoader').classList.remove('d-none');
}

function hideLoader() {
    document.getElementById('goalsContentLoader').classList.add('d-none');
}
