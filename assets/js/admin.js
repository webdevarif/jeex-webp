/**
 * Jeex WebP Admin JavaScript — Premium Redesign
 */
(function () {
    'use strict';

    let isConverting = false;
    let shouldStop = false;
    let totalConverted = 0;
    let totalSkipped = 0;
    let totalFailed = 0;
    let totalToConvert = 0;

    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        initQualitySlider();
        initFormatCards();
        initBulkConvert();
        initActions();
        loadStats();
    });

    /* ============================================
       Tabs
       ============================================ */
    function initTabs() {
        var tabs = document.querySelectorAll('.jw-tab');
        var panels = document.querySelectorAll('.jw-panel');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var target = this.dataset.tab;

                tabs.forEach(function (t) { t.classList.remove('jw-tab--active'); });
                panels.forEach(function (p) { p.classList.remove('jw-panel--active'); });

                this.classList.add('jw-tab--active');
                var panel = document.querySelector('[data-panel="' + target + '"]');
                if (panel) {
                    panel.classList.add('jw-panel--active');
                }
            });
        });
    }

    /* ============================================
       Quality Slider
       ============================================ */
    function initQualitySlider() {
        var slider = document.getElementById('quality-slider');
        var display = document.getElementById('quality-value');

        if (slider && display) {
            slider.addEventListener('input', function () {
                display.textContent = this.value + '%';
            });
        }
    }

    /* ============================================
       Format Cards
       ============================================ */
    function initFormatCards() {
        var cards = document.querySelectorAll('.jw-format-card');
        cards.forEach(function (card) {
            var radio = card.querySelector('input[type="radio"]');
            if (!radio) return;

            radio.addEventListener('change', function () {
                cards.forEach(function (c) { c.classList.remove('jw-format-card--active'); });
                if (this.checked) {
                    card.classList.add('jw-format-card--active');
                }
            });
        });
    }

    /* ============================================
       Stats
       ============================================ */
    function loadStats() {
        ajax('jeex_webp_stats', {}, function (data) {
            animateNumber('stat-total', data.total);
            animateNumber('stat-converted', data.converted);
            animateNumber('stat-unconverted', data.unconverted);
            setText('stat-saved', data.saved_formatted);
        });
    }

    function animateNumber(id, target) {
        var el = document.getElementById(id);
        if (!el) return;

        target = parseInt(target, 10) || 0;
        var current = parseInt(el.textContent, 10) || 0;

        if (current === target || el.textContent === '--') {
            el.textContent = target.toLocaleString();
            return;
        }

        var duration = 600;
        var start = performance.now();

        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            var value = Math.round(current + (target - current) * ease);
            el.textContent = value.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    }

    /* ============================================
       Bulk Conversion
       ============================================ */
    function initBulkConvert() {
        var btnConvert = document.getElementById('jeex-btn-convert');
        var btnStop = document.getElementById('jeex-btn-stop');

        if (!btnConvert) return;

        btnConvert.addEventListener('click', function () {
            if (isConverting) return;
            startBulkConversion();
        });

        if (btnStop) {
            btnStop.addEventListener('click', function () {
                shouldStop = true;
                this.style.display = 'none';
            });
        }
    }

    function startBulkConversion() {
        isConverting = true;
        shouldStop = false;
        totalConverted = 0;
        totalSkipped = 0;
        totalFailed = 0;

        var btnConvert = document.getElementById('jeex-btn-convert');
        var btnStop = document.getElementById('jeex-btn-stop');
        var progress = document.getElementById('jeex-progress');
        var badges = document.getElementById('jeex-badges');
        var log = document.getElementById('jeex-log');

        // Set loading state
        btnConvert.classList.add('jw-btn--loading');
        btnConvert.disabled = true;
        setChildText(btnConvert, '.jw-btn-text', jeexWebp.i18n.scanning);

        if (btnStop) btnStop.style.display = 'inline-flex';
        if (progress) progress.style.display = 'block';
        if (badges) badges.style.display = 'flex';
        if (log) { log.style.display = 'block'; log.innerHTML = ''; }

        updateBadges();

        ajax('jeex_webp_scan', {}, function (data) {
            if (data.unconverted === 0) {
                finishConversion(jeexWebp.i18n.noImages);
                return;
            }

            totalToConvert = data.unconverted;
            btnConvert.classList.remove('jw-btn--loading');
            setChildText(btnConvert, '.jw-btn-text', jeexWebp.i18n.converting);
            convertNextBatch();
        }, function () {
            finishConversion(jeexWebp.i18n.error);
        });
    }

    function convertNextBatch() {
        if (shouldStop) {
            finishConversion('Stopped by user.');
            return;
        }

        ajax('jeex_webp_convert_batch', { offset: 0 }, function (data) {
            totalConverted += data.converted;
            totalSkipped += data.skipped;
            totalFailed += data.failed;

            var processed = totalConverted + totalSkipped + totalFailed;
            var percent = totalToConvert > 0 ? Math.min(100, Math.round((processed / totalToConvert) * 100)) : 0;

            updateProgress(percent, processed + ' / ' + totalToConvert);
            updateBadges();

            if (data.results) {
                data.results.forEach(function (r) {
                    addLog(r.file, r.status, r.saved || r.message);
                });
            }

            if (data.done || shouldStop) {
                finishConversion(jeexWebp.i18n.complete);
                loadStats();
            } else {
                convertNextBatch();
            }
        }, function () {
            finishConversion(jeexWebp.i18n.error);
        });
    }

    function finishConversion(message) {
        isConverting = false;
        shouldStop = false;

        var btnConvert = document.getElementById('jeex-btn-convert');
        var btnStop = document.getElementById('jeex-btn-stop');

        if (btnConvert) {
            btnConvert.disabled = false;
            btnConvert.classList.remove('jw-btn--loading');
            setChildText(btnConvert, '.jw-btn-text', message || jeexWebp.i18n.complete);

            setTimeout(function () {
                setChildText(btnConvert, '.jw-btn-text', 'Start Bulk Conversion');
            }, 4000);
        }

        if (btnStop) btnStop.style.display = 'none';

        updateProgress(100, '');

        var summary = totalConverted + ' converted, ' + totalSkipped + ' skipped, ' + totalFailed + ' failed';
        addLog('Summary', 'info', summary);

        loadStats();
    }

    function updateProgress(percent, text) {
        var fill = document.getElementById('jeex-progress-fill');
        var pct = document.getElementById('jeex-progress-pct');
        var textEl = document.getElementById('jeex-progress-text');

        if (fill) fill.style.width = percent + '%';
        if (pct) pct.textContent = percent + '%';
        if (textEl) textEl.textContent = text;
    }

    function updateBadges() {
        setText('badge-converted', totalConverted);
        setText('badge-skipped', totalSkipped);
        setText('badge-failed', totalFailed);
    }

    function addLog(file, status, detail) {
        var log = document.getElementById('jeex-log');
        if (!log) return;

        var statusClass = 'jw-log-' + status;
        var entry = document.createElement('div');
        entry.className = 'jw-log-entry ' + statusClass;
        entry.innerHTML =
            '<span class="jw-log-file">' + escHtml(file) + '</span>' +
            '<span class="jw-log-status">' + escHtml(status) + '</span>' +
            (detail ? '<span class="jw-log-detail">' + escHtml(detail) + '</span>' : '');

        log.appendChild(entry);
        log.scrollTop = log.scrollHeight;
    }

    /* ============================================
       Actions
       ============================================ */
    function initActions() {
        var btnClear = document.getElementById('jeex-btn-clear-cache');
        var btnRegen = document.getElementById('jeex-btn-regen-htaccess');

        if (btnClear) {
            btnClear.addEventListener('click', function () {
                if (!confirm(jeexWebp.i18n.confirmClear)) return;

                var btn = this;
                btn.disabled = true;
                btn.classList.add('jw-btn--loading');

                ajax('jeex_webp_clear_cache', {}, function (data) {
                    btn.disabled = false;
                    btn.classList.remove('jw-btn--loading');
                    alert(data.message || jeexWebp.i18n.cleared);
                    loadStats();
                }, function () {
                    btn.disabled = false;
                    btn.classList.remove('jw-btn--loading');
                });
            });
        }

        if (btnRegen) {
            btnRegen.addEventListener('click', function () {
                this.disabled = true;
                this.classList.add('jw-btn--loading');

                var form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML =
                    '<input type="hidden" name="jeex_webp_save_settings" value="1">' +
                    '<input type="hidden" name="_wpnonce" value="' + jeexWebp.nonce + '">';
                document.body.appendChild(form);
                form.submit();
            });
        }
    }

    /* ============================================
       Helpers
       ============================================ */
    function ajax(action, data, onSuccess, onError) {
        var formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', jeexWebp.nonce);

        if (data) {
            Object.keys(data).forEach(function (key) {
                formData.append(key, data[key]);
            });
        }

        fetch(jeexWebp.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (r) {
            if (r.success && onSuccess) onSuccess(r.data);
            else if (!r.success && onError) onError(r.data);
        })
        .catch(function (err) {
            console.error('Jeex WebP:', err);
            if (onError) onError(err);
        });
    }

    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function setChildText(parent, selector, text) {
        var el = parent.querySelector(selector);
        if (el) el.textContent = text;
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
