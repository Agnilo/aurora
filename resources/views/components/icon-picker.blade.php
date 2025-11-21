<div class="icon-picker">

    {{-- Hidden input storing selected icon name --}}
    <input type="hidden" name="{{ $name }}" id="icon-picker-input-{{ $id }}" value="{{ $value }}">

    {{-- Button --}}
    <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2"
            data-bs-toggle="modal"
            data-bs-target="#iconPickerModal-{{ $id }}">
        @if($value)
            <i data-lucide="{{ $value }}"></i>
        @else
            <i data-lucide="circle"></i>
        @endif

        <span>{{ $button ?? 'Choose icon' }}</span>
    </button>


    {{-- Modal --}}
    <div class="modal fade" id="iconPickerModal-{{ $id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ $title ?? 'Choose an Icon' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Search --}}
                    <input type="text"
                           class="form-control mb-3"
                           placeholder="Search..."
                           oninput="iconPickerSearch('{{ $id }}', this.value)">

                    {{-- Icons --}}
                    <div class="icon-picker-grid" id="icon-picker-grid-{{ $id }}">
                        @foreach($icons as $icon)
                            <button type="button"
                                    class="icon-item"
                                    onclick="iconPickerSelect('{{ $id }}', '{{ $icon }}')">
                                <i data-lucide="{{ $icon }}"></i>
                                <span>{{ $icon }}</span>
                            </button>
                        @endforeach
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">{{ t('button.cancel') }}</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    function iconPickerSelect(id, icon) {
        document.getElementById("icon-picker-input-" + id).value = icon;

        // update button icon
        const btn = document.querySelector(`#iconPickerModal-${id}`)
            .closest('.icon-picker')
            .querySelector('button i');

        btn.setAttribute('data-lucide', icon);

        lucide.createIcons();
        bootstrap.Modal.getInstance(document.getElementById('iconPickerModal-' + id)).hide();
    }

    function iconPickerSearch(id, text) {
        text = text.toLowerCase();
        const items = document.querySelectorAll(`#icon-picker-grid-${id} .icon-item`);

        items.forEach(btn => {
            const name = btn.querySelector('span').innerText.toLowerCase();
            btn.style.display = name.includes(text) ? 'flex' : 'none';
        });
    }
</script>

<style>
.icon-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 12px;
}

.icon-item {
    background: #fff;
    border: 1px solid #ddd;
    padding: 10px 6px;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center;
    cursor: pointer;
    transition: 0.15s;
}
.icon-item:hover {
    background: #f3f2ec;
    border-color: #b8b29e;
}
.icon-item i {
    width: 24px;
    height: 24px;
}
.icon-item span {
    font-size: 11px;
    color: #666;
}
</style>
