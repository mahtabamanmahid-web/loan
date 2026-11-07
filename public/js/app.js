/*
 |--------------------------------------------------------------------------
 | Front-end behaviour (plain JavaScript + Alpine.js, no build step needed)
 |--------------------------------------------------------------------------
 | Loaded BEFORE Alpine (both with `defer`), so the components below are
 | registered on the `alpine:init` event.
 */

(function () {
  'use strict';

  const isBn = () => document.documentElement.lang === 'bn';
  const BN = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

  /** 12500.5 -> "৳১২,৫০০.৫০" (bn) / "৳12,500.50" (en) */
  function money(value, decimals) {
    const n = Number(value) || 0;
    const d = decimals ?? (Number.isInteger(Math.round(n * 100) / 100) ? 0 : 2);
    let s = n.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
    if (isBn()) s = s.replace(/\d/g, (x) => BN[x]);
    return '৳' + s;
  }

  function digits(value) {
    const s = String(value);
    return isBn() ? s.replace(/\d/g, (x) => BN[x]) : s;
  }

  window.money = money;
  window.digits = digits;

  /* ------------------------------------------------------------------
   | Loan maths – MUST match App\Services\LoanCalculator (flat annual rate)
   * ------------------------------------------------------------------ */
  function calculate(amount, months, annualRate, product) {
    amount = Math.max(Number(amount) || 0, 0);
    months = Math.max(Number(months) || 1, 1);
    const rate = Number(annualRate) || 0;
    const frequency = product?.repayment_frequency || 'monthly';
    const totalInterest = round2(amount * (rate / 100) * (months / 12));
    const totalPayable = round2(amount + totalInterest);
    const count = frequency === 'weekly' ? Math.round((months * 52) / 12) : months;
    let fee = 0;
    if (product) {
      const v = Number(product.processing_fee?.value ?? 0);
      fee = round2(product.processing_fee?.type === 'percent' ? (amount * v) / 100 : v);
    }
    const collection = product?.processing_fee?.collection || 'deduct';
    return {
      amount, months, rate, totalInterest, totalPayable, count,
      installment: round2(totalPayable / count),
      fee, collection,
      netDisbursement: collection === 'deduct' ? round2(amount - fee) : amount,
      frequency,
    };
  }
  const round2 = (n) => Math.round(n * 100) / 100;

  document.addEventListener('alpine:init', () => {
    /* ---------------- Theme (dark / light) ---------------- */
    Alpine.store('theme', {
      dark: document.documentElement.classList.contains('dark'),
      toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try { localStorage.setItem('theme', this.dark ? 'dark' : 'light'); } catch (e) { /* storage blocked */ }
      },
    });

    /* ---------------- Toast notifications ---------------- */
    Alpine.store('toasts', {
      items: [],
      add(type, message, timeout = 5000) {
        const id = Date.now() + Math.random();
        this.items.push({ id, type, message });
        setTimeout(() => this.remove(id), timeout);
        setTimeout(renderIcons, 30);
      },
      remove(id) { this.items = this.items.filter((t) => t.id !== id); },
    });
    window.toast = (type, msg) => Alpine.store('toasts').add(type, msg);

    /* ---------------- Interactive loan calculator ---------------- */
    Alpine.data('loanCalculator', (cfg) => calculatorState(cfg));

    /* ---------------- Apply wizard = calculator + steps ---------------- */
    Alpine.data('applyWizard', (cfg) => merge(calculatorState(cfg), {
      step: cfg.step || 1,
      total: 4,
      agree: false,
      init() {
        this.initCalculator();
        this.$watch('step', () => setTimeout(renderIcons, 50));
      },
      next() {
        if (!this.validateStep()) return;
        if (this.step === 1 && (!this.amountValid || !this.termId)) return;
        if (this.step < this.total) { this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
      },
      prev() { if (this.step > 1) { this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
      go(n) { if (n < this.step) this.step = n; },
      validateStep() {
        const panel = this.$root.querySelector(`[data-step="${this.step}"]`);
        if (!panel) return true;
        for (const f of panel.querySelectorAll('input, select, textarea')) {
          if (!f.checkValidity()) {
            f.reportValidity();
            f.classList.add('input-error');
            const box = f.closest('.field');
            box?.classList.add('animate-shake');
            setTimeout(() => box?.classList.remove('animate-shake'), 700);
            return false;
          }
          f.classList.remove('input-error');
        }
        return true;
      },
    }));
  });

  /** Merge objects while keeping getters as getters (spread would evaluate them). */
  function merge(...objects) {
    const target = {};
    for (const o of objects) Object.defineProperties(target, Object.getOwnPropertyDescriptors(o));
    return target;
  }

  function calculatorState(cfg) {
    return {
      products: cfg.products || [],
      presets: cfg.presets || [],
      productId: cfg.productId || cfg.products?.[0]?.id || null,
      termId: cfg.termId || null,
      amount: Number(cfg.amount) || 0,

      init() { this.initCalculator(); },
      initCalculator() {
        if (!this.product) this.productId = this.products[0]?.id ?? null;
        this.fixTerm();
        if (!this.amount) this.amount = Math.min(Math.max(20000, this.min), this.max);
        this.clampAmount();
        this.$watch('productId', () => { this.fixTerm(); this.clampAmount(); });
      },
      get product() { return this.products.find((p) => p.id == this.productId) || null; },
      get terms() { return this.product?.terms || []; },
      get term() { return this.terms.find((t) => t.id == this.termId) || null; },
      get min() { return Number(this.product?.min_amount || cfg.min || 0); },
      get max() { return Number(this.product?.max_amount || cfg.max || 0); },
      get visiblePresets() { return this.presets.filter((p) => p >= this.min && p <= this.max); },
      get result() { return calculate(this.amount, this.term?.months || 1, this.term?.interest_rate || 0, this.product); },
      get amountValid() { return this.amount >= this.min && this.amount <= this.max; },
      get progress() { return this.max > this.min ? ((this.amount - this.min) / (this.max - this.min)) * 100 : 0; },
      fixTerm() {
        if (!this.terms.find((t) => t.id == this.termId)) this.termId = this.terms[Math.floor(this.terms.length / 2)]?.id ?? this.terms[0]?.id ?? null;
      },
      clampAmount() {
        this.amount = Math.round(Math.min(Math.max(Number(this.amount) || this.min, this.min), this.max));
      },
      money, digits,
    };
  }

  document.addEventListener('alpine:init', () => {
    /* ---------------- Eligibility checker ---------------- */
    Alpine.data('eligibility', (cfg) => ({
      income: '', obligations: '', amount: cfg.amount || 20000, months: cfg.months || 6, rate: cfg.rate || 12,
      loading: false, result: null, error: null,
      async check() {
        this.loading = true; this.error = null;
        try {
          const res = await fetch(cfg.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ monthly_income: this.income || 0, monthly_obligations: this.obligations || 0, amount: this.amount, months: this.months, interest_rate: this.rate }),
          });
          const json = await res.json();
          if (!res.ok) throw new Error(json.message || 'Error');
          this.result = json.data;
        } catch (e) {
          this.error = e.message;
        } finally {
          this.loading = false;
          setTimeout(renderIcons, 30);
        }
      },
      money, digits,
    }));

    /* ---------------- Simple repayment calculator (no product) ---------------- */
    Alpine.data('repaymentCalc', (cfg) => ({
      amount: cfg.amount || 50000, months: cfg.months || 12, rate: cfg.rate || 12,
      get r() { return calculate(this.amount, this.months, this.rate, null); },
      money, digits,
    }));
  });

  /* ------------------------------------------------------------------
   | Lucide icons: render <i data-lucide="..."> and re-render on DOM changes
   * ------------------------------------------------------------------ */
  let iconTimer = null;
  function renderIcons() {
    if (window.lucide) window.lucide.createIcons({ attrs: { 'stroke-width': 2 } });
  }
  window.renderIcons = renderIcons;

  document.addEventListener('DOMContentLoaded', () => {
    renderIcons();
    new MutationObserver(() => {
      clearTimeout(iconTimer);
      iconTimer = setTimeout(() => {
        if (document.querySelector('i[data-lucide]')) renderIcons();
      }, 40);
    }).observe(document.body, { childList: true, subtree: true });

    if (window.AOS) window.AOS.init({ once: true, duration: 600, offset: 40, easing: 'ease-out-cubic' });
  });

  /* ------------------------------------------------------------------
   | Forms: confirmation dialogs + loading state on submit buttons
   |   <form data-confirm="Are you sure?">   -> asks before submitting
   |   <form data-no-loading>                -> skip spinner (e.g. file downloads)
   * ------------------------------------------------------------------ */
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
      e.preventDefault();
      return;
    }
    if (e.defaultPrevented || form.hasAttribute('data-no-loading')) return;

    const buttons = form.querySelectorAll('button[type="submit"], button:not([type])');
    buttons.forEach((b) => {
      b.classList.add('is-loading');
      b.setAttribute('aria-busy', 'true');
    });
    // Re-enable after 15s in case the browser stays on the page (e.g. download)
    setTimeout(() => buttons.forEach((b) => b.classList.remove('is-loading')), 15000);
  }, false);

  // Coming back with the browser "back" button must not leave spinners running
  window.addEventListener('pageshow', () => {
    document.querySelectorAll('.is-loading').forEach((b) => b.classList.remove('is-loading'));
  });
})();
