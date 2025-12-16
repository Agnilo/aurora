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
                top: rect.top,
                left: rect.left,
                width: rect.width,
                height: rect.height
            };
        });
    }

    function animateFLIP(first, last) {
        first.forEach((f, i) => {
            const l = last[i];

            //
            // HORIZONTAL-ONLY FLIP
            //

            // Only horizontal movement:
            const dx = f.left - l.left;

            // Only horizontal stretch/shrink:
            const sx = f.width / l.width;

            // INVERT (no vertical compensation)
            l.el.style.transform = `translateX(${dx}px) scaleX(${sx})`;
            l.el.style.transition = "transform 0s";

            // PLAY (animate to natural position)
            requestAnimationFrame(() => {
                l.el.style.transform = `translateX(0px) scaleX(1)`;
                l.el.style.transition =
                    "transform 320ms cubic-bezier(.25,.8,.25,1)";
            });
        });
    }

    //
    // ===========================================================
    // MAIN CLICK HANDLER — FLIP + AJAX
    // ===========================================================
    //

    tiles.forEach(tile => {
        tile.addEventListener('click', () => {

            const categoryId = tile.dataset.category;
            const url = categoryId
                ? `${window.location.origin}${window.location.pathname}?category=${categoryId}&ajax=1`
                : `${window.location.origin}${window.location.pathname}?ajax=1`;

            const isReset = tile.classList.contains('active');

            //
            // 1) FIRST — measure before layout state change
            //
            const first = getBoxes(tiles);

            //
            // APPLY UI STATE: expand or reset
            //
            if (isReset) {
                tiles.forEach(t => t.classList.remove('active'));
                grid.classList.remove('has-active');
            } else {
                tiles.forEach(t => t.classList.remove('active'));
                tile.classList.add('active');
                grid.classList.add('has-active');
            }

            //
            // 2) LAST — measure after layout state change
            //
            requestAnimationFrame(() => {
                const last = getBoxes(tiles);

                //
                // 3) Animate HORIZONTAL FLIP
                //
                animateFLIP(first, last);
            });

            //
            // ===========================================================
            // AJAX GOALS LOADING (unchanged)
            // ===========================================================
            //

            window.slideDirection =
                lastCategory === null || isReset
                    ? "left"
                    : (categoryId > lastCategory ? "left" : "right");

            lastCategory = categoryId;

            showLoader();

            fadeOutAndHide(goalsContent, () => {

                minimumDelay(
                    fetch(url, {
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    }).then(r => r.text()),
                    500
                )
                .then(html => {
                    hideLoader();
                    fadeInWithContent(goalsContent, html);
                });
            });
        });
    });


    //
    // ===========================================================
    // EFFECT HELPERS — original functions kept
    // ===========================================================
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

});
