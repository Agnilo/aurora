@once
@php
    $toggleTaskUrl = route('tasks.toggle-complete', [
        'locale' => app()->getLocale(),
        'task' => '__TASK__',
    ]);
@endphp

<script>
(() => {
    const toggleTaskUrl = @json($toggleTaskUrl);
    const csrfToken = @json(csrf_token());

    const setText = (id, value) => {
        const element = document.getElementById(id);

        if (element && value !== undefined && value !== null) {
            element.textContent = value;
        }
    };

    const setText = (id, value) => {
        const element = document.getElementById(id);

        if (element && value !== undefined && value !== null) {
            element.textContent = value;
        }
    };

    const clampPercent = (value) => {
        const number = Number(value);

        return Number.isFinite(number)
            ? Math.max(0, Math.min(100, number))
            : 0;
    };

    document.addEventListener("change", async (event) => {
        const checkbox = event.target;

        if (
            !(checkbox instanceof HTMLInputElement) ||
            !checkbox.matches(".task-done-toggle")
        ) {
            return;
        }

        if (checkbox.dataset.loading === "1") {
            return;
        }

        const previousChecked = !checkbox.checked;

        checkbox.dataset.loading = "1";
        checkbox.disabled = true;

        const url = toggleTaskUrl.replace(
            "__TASK__",
            encodeURIComponent(checkbox.dataset.taskId)
        );

        try {
            const response = await fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) {
                throw new Error("Request failed");
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error("Task update failed");
            }

            checkbox.checked = Boolean(data.completed_at);

            // Užduoties būsena.
            const taskItem = checkbox.closest(".list-group-item");
            const badge = taskItem?.querySelector(".task-status-badge");

            if (badge) {
                badge.textContent = data.status_label ?? "";
                badge.style.background = data.status_color || "#ccc";
            }

            // Etapo progresas.
            const milestone = checkbox.closest(".milestone-item");

            if (milestone && data.milestone_progress != null) {
                const percent = clampPercent(data.milestone_progress);
                const bar = milestone.querySelector(
                    ".milestone-progress-bar"
                );
                const label = milestone.querySelector(
                    ".milestone-progress-label"
                );

                if (bar) {
                    bar.style.width = `${percent}%`;
                    bar.setAttribute("aria-valuenow", percent);
                }

                if (label) {
                    label.textContent = `${percent}%`;
                }
            }

            // Tikslo progresas.
            const goal = checkbox.closest(".accordion-item");
            const goalBadge = goal?.querySelector(".goal-progress-badge");

            if (goalBadge && data.goal_progress != null) {
                goalBadge.textContent =
                    `${clampPercent(data.goal_progress)}%`;
            }

            // Bendri naudotojo rodikliai.
            setText("g-level", data.level);
            setText("g-coins", data.coins);
            setText("g-xp", data.xp);
            setText("g-xp-next", data.xp_next);

            const xpBar = document.getElementById("g-xp-bar");

            if (xpBar && data.xp != null && data.xp_next != null) {
                const maximum = Number(data.xp_next);
                const percent = maximum > 0
                    ? clampPercent((Number(data.xp) / maximum) * 100)
                    : 0;

                xpBar.style.width = `${percent}%`;
                xpBar.setAttribute("aria-valuenow", percent);
            }

            // Kategorijų progreso juostos.
            const grid = document.getElementById("categoryGrid");

            if (grid && data.category_xp) {
                Object.entries(data.category_xp).forEach(
                    ([categoryId, xpData]) => {
                        const link = grid.querySelector(
                            `.category-link[data-category-id="${categoryId}"]`
                        );

                        if (!link) {
                            return;
                        }

                        const progressBar = link.querySelector(
                            ".gc-progress"
                        );
                        const progressFill = progressBar?.querySelector(
                            "span"
                        );
                        const label = link.querySelector(
                            ".life-area-progress small"
                        );

                        const xp = Number(xpData.xp ?? 0);

                        // Naudojame tą patį maksimumą kaip Blade kortelėje.
                        const maximum = Number(
                            progressBar?.dataset.max ??
                            xpData.xp_next ??
                            100
                        );

                        const percent = maximum > 0
                            ? clampPercent((xp / maximum) * 100)
                            : 0;

                        if (progressFill) {
                            progressFill.style.width = `${percent}%`;
                        }

                        if (progressBar) {
                            progressBar.setAttribute(
                                "aria-valuenow",
                                percent
                            );
                        }

                        if (label) {
                            const unit = label.dataset.unit ?? "";

                            label.textContent =
                                `${xp} / ${maximum} ${unit}`.trim();
                        }
                    }
                );
            }
        } catch (error) {
            checkbox.checked = previousChecked;

            console.error(error);
            alert("Įvyko klaida, pabandyk vėliau.");
        } finally {
            checkbox.disabled = false;
            checkbox.dataset.loading = "0";
        }
    });
})();
</script>
@endonce