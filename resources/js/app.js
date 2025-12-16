import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {

    const goalsContent = document.getElementById('goalsDynamic');
    const loader = document.getElementById('goalsContentLoader');
    const grid = document.getElementById('categoryGrid');

    if (grid && goalsContent && loader) {

        // Tiles are only in the category grid (never replaced by AJAX)
        const tiles = grid.querySelectorAll('.category-link');
        if (!tiles.length) return;

        // JS is the single source of truth after initial render
        let currentCategoryId = grid.classList.contains('has-active')
            ? (grid.querySelector('.category-link.active')?.dataset.categoryId ?? null)
            : null;

        let lastCategoryId = currentCategoryId; // for slideDirection

        //
        // ===========================================================
        // FLIP HELPERS — HORIZONTAL ONLY
        // ===========================================================
        //

        function getBoxes(elements) {
            return [...elements].map(el => {
                const rect = el.getBoundingClientRect();
                return {
                    el,
                    left: rect.left,
                    width: rect.width,
                };
            });
        }

        function animateFLIP(first, last) {
            first.forEach((f, i) => {
                const l = last[i];
                if (!l) return;

                const dx = f.left - l.left;
                const sx = f.width / l.width;

                l.el.style.transform = `translateX(${dx}px) scaleX(${sx})`;
                l.el.style.transition = "transform 0s";

                requestAnimationFrame(() => {
                    l.el.style.transform = `translateX(0px) scaleX(1)`;
                    l.el.style.transition = "transform 320ms cubic-bezier(.25,.8,.25,1)";
                });
            });
        }

        //
        // ===========================================================
        // UI STATE
        // ===========================================================
        //

        function setActive(categoryId) {
            tiles.forEach(t => t.classList.remove('active'));

            if (categoryId) {
                const activeTile = [...tiles].find(t => t.dataset.categoryId === String(categoryId));
                if (activeTile) activeTile.classList.add('active');
                grid.classList.add('has-active');
            } else {
                grid.classList.remove('has-active');
            }
        }

        function buildUrl(categoryId) {
            const base = `${window.location.origin}${window.location.pathname}`;
            return categoryId
                ? `${base}?category=${categoryId}&ajax=1`
                : `${base}?ajax=1`;
        }

        //
        // ===========================================================
        // MAIN CLICK HANDLER — FLIP + AJAX
        // ===========================================================
        //

        tiles.forEach(tile => {
            tile.addEventListener('click', (e) => {
                e.preventDefault();

                const clickedId = tile.dataset.categoryId ?? null;
                const isReset = (currentCategoryId !== null && String(currentCategoryId) === String(clickedId));

                const nextCategoryId = isReset ? null : clickedId;
                const url = buildUrl(nextCategoryId);

                // 1) FIRST
                const first = getBoxes(tiles);

                // Apply UI state
                setActive(nextCategoryId);

                // 2) LAST + FLIP
                requestAnimationFrame(() => {
                    const last = getBoxes(tiles);
                    animateFLIP(first, last);
                });

                // Slide direction
                window.slideDirection =
                    lastCategoryId === null || isReset
                        ? "left"
                        : (String(clickedId) > String(lastCategoryId) ? "left" : "right");

                lastCategoryId = nextCategoryId;
                currentCategoryId = nextCategoryId;

                showLoader();

                fadeOutAndHide(goalsContent, () => {
                    minimumDelay(
                        fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } }).then(r => r.text()),
                        300
                    ).then(html => {
                        hideLoader();
                        fadeInWithContent(goalsContent, html);
                    });
                });
            });
        });

        //
        // ===========================================================
        // EFFECT HELPERS — kept as you had
        // ===========================================================
        //

        function fadeOutAndHide(element, callback) {
            element.style.transition = "opacity 0.45s ease, filter 0.45s ease";
            element.style.opacity = 0;
            element.style.filter = "blur(8px)";

            setTimeout(() => {
                element.style.display = "none";
                callback();
            }, 250);
        }

        function fadeInWithContent(container, newHtml) {
            container.style.display = "block";
            container.innerHTML = newHtml;

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
            loader.classList.remove('d-none');
        }

        function hideLoader() {
            loader.classList.add('d-none');
        }
    }

    document.querySelectorAll('.featured-carousel').forEach(carousel => {

        const track   = carousel.querySelector('.carousel-track');
        const nextBtn = carousel.querySelector('.carousel-btn.next');
        const prevBtn = carousel.querySelector('.carousel-btn.prev');
        const cards   = carousel.querySelectorAll('.featured-card');

        if (!track || cards.length < 2) return;

        const GAP = 16;          // turi sutapti su CSS gap
        const AUTO_DELAY = 10000;

        function cardStep() {
            return cards[0].offsetWidth + GAP;
        }

        function next() {
            track.scrollBy({ left: cardStep(), behavior: 'smooth' });
        }

        function prev() {
            track.scrollBy({ left: -cardStep(), behavior: 'smooth' });
        }

        nextBtn?.addEventListener('click', next);
        prevBtn?.addEventListener('click', prev);

        // AUTO SCROLL (simple)
        let autoTimer = setInterval(next, AUTO_DELAY);

        carousel.addEventListener('mouseenter', () => clearInterval(autoTimer));
        carousel.addEventListener('mouseleave', () => {
            autoTimer = setInterval(next, AUTO_DELAY);
        });

    });


});
