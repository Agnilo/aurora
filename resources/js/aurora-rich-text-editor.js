import '../css/frontend/components/aurora-rich-text-editor.css';

const FIELD_SELECTOR = '#goal-form textarea[name="description"], form[data-plan-editor="task"] textarea[name="description"], form[data-plan-editor="habit"] textarea[name="description"], textarea[data-rich-text]';
const ALLOWED_TAGS = new Set(['p', 'div', 'h3', 'h4', 'strong', 'em', 'u', 's', 'ul', 'ol', 'li', 'br', 'span', 'a']);
const BLOCK_TAGS = new Set(['p', 'div', 'h3', 'h4', 'ul', 'ol', 'li']);
const MAX_DEPTH = 30;
const MAX_NODES = 4000;
const enhanced = new WeakSet();
let nextId = 0;

function readConfig(template) {
    return {
        labels: JSON.parse(template.dataset.labels),
        rows: JSON.parse(template.dataset.textColors),
        highlights: JSON.parse(template.dataset.highlightColors),
        prefix: template.dataset.prefix,
    };
}

function validUrl(value) {
    if (typeof value !== 'string' || value.length > 2048 || !/^https?:\/\//i.test(value) || /[\u0000-\u0020\u007f\\]/.test(value)) return null;
    try {
        const parsed = new URL(value);
        return ['http:', 'https:'].includes(parsed.protocol) && parsed.hostname ? parsed.href : null;
    } catch {
        return null;
    }
}

function asHex(value) {
    if (!value) return null;
    if (/^#[0-9a-f]{6}$/i.test(value)) return value.toLowerCase();
    const rgb = value.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*(\d+(?:\.\d+)?))?\s*\)$/i);
    if (!rgb || (rgb[4] !== undefined && Number(rgb[4]) !== 1)) return null;
    const parts = rgb.slice(1, 4).map(Number);
    if (parts.some(n => n > 255)) return null;
    return '#' + parts.map(n => n.toString(16).padStart(2, '0')).join('');
}

function allowedColor(value, palette) {
    const hex = asHex(value);
    return palette.includes(hex) ? hex : null;
}

function decodeValue(value, config) {
    if (!value.startsWith(config.prefix)) return null;
    try {
        const data = JSON.parse(value.slice(config.prefix.length));
        if (!Array.isArray(data)) return null;
        let count = 0;
        function validate(nodes, depth = 0) {
            if (depth > MAX_DEPTH || !Array.isArray(nodes)) throw new Error('rich text');
            return nodes.flatMap(node => {
                if (++count > MAX_NODES) throw new Error('rich text');
                if (typeof node === 'string') return [node];
                if (!Array.isArray(node) || node.length !== 3 || typeof node[0] !== 'string' || !node[1] || typeof node[1] !== 'object' || !Array.isArray(node[2])) throw new Error('rich text');
                let [tag, source, children] = node;
                children = validate(children, depth + 1);
                if (!ALLOWED_TAGS.has(tag)) return children;
                const attrs = {};
                if (config.rows.flat().includes(source.color)) attrs.color = source.color;
                if (config.highlights.includes(source.background)) attrs.background = source.background;
                if (tag === 'a') {
                    const url = validUrl(source.href);
                    if (url) attrs.href = url;
                    else tag = 'span';
                }
                return [[tag, tag === 'br' ? {} : attrs, tag === 'br' ? [] : children]];
            });
        }
        return validate(data);
    } catch {
        return null;
    }
}

function renderNodes(nodes, parent) {
    for (const node of nodes) {
        if (typeof node === 'string') {
            parent.append(document.createTextNode(node));
            continue;
        }
        const [tag, attrs, children] = node;
        const element = document.createElement(tag);
        if (attrs.color) element.style.color = attrs.color;
        if (attrs.background) element.style.backgroundColor = attrs.background;
        if (tag === 'a' && attrs.href) {
            element.href = attrs.href;
            element.rel = 'noopener noreferrer nofollow';
        }
        renderNodes(children, element);
        parent.append(element);
    }
}

function loadValue(editor, value, config) {
    editor.replaceChildren();
    const nodes = decodeValue(value, config);
    if (nodes === null) editor.textContent = value;
    else renderNodes(nodes, editor);
}

function serializeEditor(editor, config) {
    let count = 0;
    const palette = config.rows.flat();
    function visit(parent, depth = 0) {
        if (depth > MAX_DEPTH) throw new Error('rich text depth');
        const result = [];
        for (const node of parent.childNodes) {
            if (++count > MAX_NODES) throw new Error('rich text nodes');
            if (node.nodeType === Node.TEXT_NODE) {
                if (node.nodeValue) result.push(node.nodeValue);
                continue;
            }
            if (node.nodeType !== Node.ELEMENT_NODE) continue;
            let tag = node.tagName.toLowerCase();
            if (['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'img', 'video', 'audio'].includes(tag)) continue;
            const children = tag === 'br' ? [] : visit(node, depth + 1);
            const aliases = { b: 'strong', i: 'em', strike: 's', del: 's', font: 'span', mark: 'span', h1: 'h3', h2: 'h3', h5: 'h4', h6: 'h4' };
            tag = aliases[tag] || tag;
            if (!ALLOWED_TAGS.has(tag)) {
                result.push(...children);
                continue;
            }
            const attrs = {};
            const color = allowedColor(node.style.color || node.getAttribute('color'), palette);
            const background = allowedColor(node.style.backgroundColor, config.highlights);
            if (color) attrs.color = color;
            if (background) attrs.background = background;
            if (tag === 'a') {
                const href = validUrl(node.getAttribute('href'));
                if (href) attrs.href = href;
                else tag = 'span';
            }
            if (tag === 'br') {
                result.push(['br', {}, []]);
                continue;
            }
            let content = children;
            const weight = node.style.fontWeight;
            const decoration = node.style.textDecorationLine || node.style.textDecoration || '';
            if (tag !== 'strong' && (weight === 'bold' || Number(weight) >= 600)) content = [['strong', {}, content]];
            if (tag !== 'em' && /^(italic|oblique)/.test(node.style.fontStyle)) content = [['em', {}, content]];
            if (tag !== 'u' && decoration.includes('underline')) content = [['u', {}, content]];
            if (tag !== 's' && decoration.includes('line-through')) content = [['s', {}, content]];
            if (tag === 'span' && Object.keys(attrs).length === 0) result.push(...content);
            else result.push([tag, attrs, content]);
        }
        return result;
    }
    return visit(editor);
}

function isFormatted(nodes) {
    return nodes.some(node => typeof node !== 'string' && (
        !['p', 'div', 'br'].includes(node[0]) || Object.keys(node[1]).length > 0 || isFormatted(node[2])
    ));
}

function contrastColor(hex) {
    const rgb = [1, 3, 5].map(index => parseInt(hex.slice(index, index + 2), 16) / 255);
    const luminance = rgb.map(n => n <= .04045 ? n / 12.92 : ((n + .055) / 1.055) ** 2.4)
        .reduce((sum, n, i) => sum + n * [.2126, .7152, .0722][i], 0);
    return luminance > .179 ? '#28222c' : '#ffffff';
}

function positionPopup(panel, trigger) {
    const rect = trigger.getBoundingClientRect();
    const box = panel.getBoundingClientRect();
    const viewportWidth = document.documentElement.clientWidth;
    const viewportHeight = document.documentElement.clientHeight;
    const top = rect.bottom + box.height + 8 <= viewportHeight ? rect.bottom + 7 : Math.max(8, rect.top - box.height - 7);
    panel.style.left = Math.max(8, Math.min(rect.left, viewportWidth - box.width - 8)) + 'px';
    panel.style.top = top + 'px';
}

function enhance(field, template, config) {
    if (enhanced.has(field) || field.disabled || field.readOnly || !field.form) return;
    enhanced.add(field);
    const root = template.content.firstElementChild.cloneNode(true);
    const editor = root.querySelector('.aurora-rte-body');
    const toolbar = root.querySelector('.aurora-rte-toolbar');
    const format = root.querySelector('.aurora-rte-format');
    const count = root.querySelector('[data-rte-count]');
    const error = root.querySelector('[data-rte-error]');
    const colorTrigger = root.querySelector('[data-rte-popup="colors"]');
    const form = field.form;
    const maxLength = field.maxLength > 0 ? field.maxLength : 10000;
    const uniqueId = `aurora-rte-${++nextId}`;
    if (!field.id) field.id = `${uniqueId}-source`;
    editor.id = `${uniqueId}-body`;
    editor.setAttribute('aria-label', config.labels.description);
    error.id = `${uniqueId}-error`;
    editor.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
    if (field.getAttribute('aria-invalid') === 'true') editor.setAttribute('aria-invalid', 'true');
    if (field.required) editor.setAttribute('aria-required', 'true');

    const originalLabels = Array.from(field.labels || []);
    const wrappedLabel = field.closest('label');
    if (wrappedLabel) {
        const wrapper = document.createElement('div');
        wrapper.className = 'aurora-rte-field';
        wrappedLabel.before(wrapper);
        wrapper.append(wrappedLabel);
        wrapper.append(field);
        field.after(root);
        wrappedLabel.htmlFor = field.id;
    } else {
        field.after(root);
    }
    originalLabels.forEach((label, index) => {
        if (!label.id) label.id = `${uniqueId}-label-${index}`;
        label.addEventListener('click', event => {
            event.preventDefault();
            editor.focus();
        });
    });
    if (originalLabels.length) editor.setAttribute('aria-labelledby', originalLabels.map(label => label.id).join(' '));

    loadValue(editor, field.value, config);
    field.hidden = true;
    field.classList.add('aurora-rte-source');
    field.tabIndex = -1;
    let savedRange = null;
    let issue = '';
    let dirty = false;
    let activePanel = null;
    let activeTrigger = null;
    let linkRange = null;
    let editingLink = null;
    let restoringSelection = false;

    function selectionInside() {
        const selection = document.getSelection();
        if (!selection?.rangeCount) return null;
        const range = selection.getRangeAt(0);
        return editor.contains(range.startContainer) && editor.contains(range.endContainer) ? range : null;
    }

    function captureSelection() {
        const range = selectionInside();
        if (range && !restoringSelection && !activePanel) savedRange = range.cloneRange();
        return range;
    }

    function restoreSelection(preferred = savedRange) {
        const range = preferred && editor.contains(preferred.startContainer) && editor.contains(preferred.endContainer) ? preferred.cloneRange() : document.createRange();
        if (!preferred || !editor.contains(preferred.startContainer) || !editor.contains(preferred.endContainer)) {
            range.selectNodeContents(editor);
            range.collapse(false);
        }
        restoringSelection = true;
        editor.focus({ preventScroll: true });
        const selection = document.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        restoringSelection = false;
        savedRange = range.cloneRange();
    }

    function refreshToolbar() {
        if (!selectionInside()) return;
        toolbar.querySelectorAll('[data-rte-command][aria-pressed]').forEach(button => {
            try { button.setAttribute('aria-pressed', String(document.queryCommandState(button.dataset.rteCommand))); } catch { }
        });
        let block = String(document.queryCommandValue('formatBlock')).toLowerCase();
        if (!['h3', 'h4'].includes(block)) block = 'p';
        format.value = block;
        const currentColor = allowedColor(document.queryCommandValue('foreColor'), config.rows.flat()) || config.rows[0][0];
        const currentBackground = allowedColor(document.queryCommandValue('hiliteColor'), config.highlights);
        colorTrigger.style.setProperty('--rte-current-color', currentColor);
        root.querySelectorAll('[data-rte-color]').forEach(button => {
            const selected = button.dataset.rteKind === 'color' ? currentColor === button.dataset.rteColor : currentBackground === button.dataset.rteColor;
            button.setAttribute('aria-pressed', String(selected));
        });
    }

    function report(message = '') {
        issue = message;
        error.textContent = message;
        error.hidden = !message;
        editor.setAttribute('aria-invalid', String(!!message));
        root.classList.toggle('is-invalid', !!message);
        const size = Array.from(field.value).length;
        count.textContent = `${size} / ${maxLength}`;
        count.hidden = size < maxLength * .9;
        return !message;
    }

    function sync() {
        if (dirty) {
            try {
                const nodes = serializeEditor(editor, config);
                const plainText = editor.innerText.replace(/\u200b/g, '');
                const value = !plainText.trim() ? '' : isFormatted(nodes) || plainText.startsWith(config.prefix) ? config.prefix + JSON.stringify(nodes) : plainText;
                dirty = false;
                if (field.value !== value) {
                    field.value = value;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                }
            } catch {
                return report(config.labels.too_complex);
            }
        }
        return report(Array.from(field.value).length > maxLength ? config.labels.too_long : '');
    }

    function afterEdit() {
        dirty = true;
        captureSelection();
        sync();
        refreshToolbar();
    }

    function command(name, value = null) {
        restoreSelection();
        if (name === 'clearFormat') {
            document.execCommand('removeFormat', false, null);
            document.execCommand('unlink', false, null);
            if (document.queryCommandState('insertUnorderedList')) document.execCommand('insertUnorderedList', false, null);
            if (document.queryCommandState('insertOrderedList')) document.execCommand('insertOrderedList', false, null);
            const block = String(document.queryCommandValue('formatBlock')).toLowerCase();
            if (['h3', 'h4'].includes(block)) document.execCommand('formatBlock', false, 'p');
        } else {
            const withCss = ['foreColor', 'hiliteColor', 'backColor'].includes(name);
            document.execCommand('styleWithCSS', false, withCss);
            document.execCommand(name, false, value);
            if (withCss) document.execCommand('styleWithCSS', false, false);
        }
        afterEdit();
    }

    function closePanel() {
        if (activePanel?.matches(':popover-open')) activePanel.hidePopover();
        if (activeTrigger) activeTrigger.setAttribute('aria-expanded', 'false');
        activePanel = null;
        activeTrigger = null;
    }

    function openPanel(name, trigger) {
        captureSelection();
        const panel = root.querySelector(`[data-rte-panel="${name}"]`);
        if (panel === activePanel && panel.matches(':popover-open')) { closePanel(); return; }
        closePanel();
        activePanel = panel;
        activeTrigger = trigger;
        trigger.setAttribute('aria-expanded', 'true');
        if (name === 'link') {
            linkRange = savedRange?.cloneRange() ?? null;
            const input = panel.querySelector('[data-rte-link-url]');
            const anchorNode = linkRange?.startContainer;
            const link = (anchorNode?.nodeType === Node.ELEMENT_NODE ? anchorNode : anchorNode?.parentElement)?.closest('a');
            editingLink = link && editor.contains(link) && link.contains(linkRange.endContainer) ? link : null;
            input.value = editingLink?.getAttribute('href') || '';
            panel.querySelector('[data-rte-link-text]').value = editingLink?.textContent || linkRange?.toString() || '';
            panel.querySelector('[data-rte-link-error]').hidden = true;
        }
        panel.showPopover();
        positionPopup(panel, trigger);
        if (name === 'link') panel.querySelector('[data-rte-link-url]').focus({ preventScroll: true });
    }

    for (const panel of root.querySelectorAll('[data-rte-panel]')) {
        panel.id = `${uniqueId}-${panel.dataset.rtePanel}`;
        root.querySelector(`[data-rte-popup="${panel.dataset.rtePanel}"]`)?.setAttribute('aria-controls', panel.id);
        panel.addEventListener('toggle', event => {
            if (event.newState === 'closed' && activePanel === panel) closePanel();
        });
    }

    const swatchGroups = { color: config.rows.flat(), background: config.highlights };
    for (const [kind, colors] of Object.entries(swatchGroups)) {
        const palette = root.querySelector(`[data-rte-palette="${kind}"]`);
        colors.forEach((color, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'aurora-rte-swatch';
            button.dataset.rteKind = kind;
            button.dataset.rteColor = color;
            button.style.setProperty('--rte-swatch', color);
            button.style.setProperty('--rte-swatch-ink', contrastColor(color));
            button.title = `${config.labels[kind === 'color' ? 'text_color' : 'highlight']} ${color.toUpperCase()}`;
            button.setAttribute('aria-label', button.title);
            button.setAttribute('aria-pressed', String(kind === 'color' && index === 0));
            if (kind === 'background') button.textContent = 'A';
            palette.append(button);
        });
    }

    root.addEventListener('mousedown', event => {
        if (event.target.closest('button')) event.preventDefault();
    });
    root.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button || !root.contains(button)) return;
        if (button.dataset.rteCommand) {
            command(button.dataset.rteCommand);
        } else if (button.dataset.rtePopup) {
            openPanel(button.dataset.rtePopup, button);
        } else if (button.dataset.rteColor) {
            closePanel();
            command(button.dataset.rteKind === 'color' ? 'foreColor' : 'hiliteColor', button.dataset.rteColor);
        } else if (button.dataset.rteReset) {
            closePanel();
            command(button.dataset.rteReset === 'color' ? 'foreColor' : 'hiliteColor', button.dataset.rteReset === 'color' ? config.rows[0][0] : 'transparent');
        } else if (button.hasAttribute('data-rte-unlink')) {
            const target = linkRange?.cloneRange();
            closePanel();
            if (target) savedRange = target;
            command('unlink');
            editingLink = null;
            linkRange = null;
        } else if (button.hasAttribute('data-rte-link-apply')) {
            applyLink();
        }
    });

    function applyLink() {
        const panel = root.querySelector('[data-rte-panel="link"]');
        const input = panel.querySelector('[data-rte-link-url]');
        const url = validUrl(input.value.trim());
        const invalid = panel.querySelector('[data-rte-link-error]');
        invalid.hidden = !!url;
        if (!url) { input.focus(); return; }
        const text = panel.querySelector('[data-rte-link-text]').value.trim();
        let targetRange = linkRange?.cloneRange() ?? null;
        if (editingLink && editor.contains(editingLink)) {
            targetRange = document.createRange();
            targetRange.selectNode(editingLink);
        }
        const selectedText = targetRange?.toString() || '';
        closePanel();
        restoreSelection(targetRange);
        const link = document.createElement('a');
        link.href = url;
        link.textContent = text || selectedText || url;
        if (!editingLink && text === selectedText && selectedText && !selectionInside()?.collapsed) {
            document.execCommand('createLink', false, url);
        } else {
            document.execCommand('insertHTML', false, link.outerHTML);
        }
        editingLink = null;
        linkRange = null;
        afterEdit();
    }

    root.querySelectorAll('[data-rte-link-url], [data-rte-link-text]').forEach(input => {
        input.addEventListener('keydown', event => {
            if (event.key === 'Enter') { event.preventDefault(); applyLink(); }
        });
    });
    format.addEventListener('mousedown', captureSelection);
    format.addEventListener('change', () => command('formatBlock', format.value));
    editor.addEventListener('input', afterEdit);
    editor.addEventListener('paste', event => {
        event.preventDefault();
        const text = event.clipboardData?.getData('text/plain') || '';
        if (text) document.execCommand('insertText', false, text);
        afterEdit();
    });
    editor.addEventListener('drop', event => event.preventDefault());
    editor.addEventListener('click', event => {
        if (event.target.closest('a')) event.preventDefault();
    });
    editor.addEventListener('focus', () => { captureSelection(); refreshToolbar(); });
    document.addEventListener('selectionchange', () => {
        if (captureSelection()) refreshToolbar();
    });
    window.addEventListener('resize', () => { if (activePanel && activeTrigger) positionPopup(activePanel, activeTrigger); });
    document.addEventListener('scroll', () => { if (activePanel && activeTrigger) positionPopup(activePanel, activeTrigger); }, true);
    form.addEventListener('submit', event => {
        if (!sync() || issue) {
            event.preventDefault();
            event.stopImmediatePropagation();
            const details = root.closest('details');
            if (details) details.open = true;
            editor.focus();
            root.scrollIntoView({ block: 'nearest' });
        }
    }, true);
    form.addEventListener('reset', () => setTimeout(() => {
        dirty = false;
        savedRange = null;
        loadValue(editor, field.value, config);
        sync();
    }, 0));

    const initialSize = Array.from(field.value).length;
    count.textContent = `${initialSize} / ${maxLength}`;
    count.hidden = initialSize < maxLength * .9;
}

function initialize() {
    const template = document.getElementById('aurora-rich-text-template');
    if (!template || typeof HTMLElement.prototype.showPopover !== 'function' || typeof document.execCommand !== 'function') return;
    const config = readConfig(template);
    const scan = node => {
        if (node.nodeType === Node.ELEMENT_NODE && node.matches(FIELD_SELECTOR)) enhance(node, template, config);
        node.querySelectorAll?.(FIELD_SELECTOR).forEach(field => enhance(field, template, config));
    };
    scan(document);
    const observer = new MutationObserver(records => {
        for (const record of records) for (const node of record.addedNodes) {
            if (node.nodeType === Node.ELEMENT_NODE) scan(node);
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
else initialize();
