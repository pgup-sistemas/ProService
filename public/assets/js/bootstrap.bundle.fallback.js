/*
  Lightweight local fallback for Bootstrap JS (bundle) — shim only for Modal + backdrop.
  PURPOSE: ensure modals (onboarding, preview, global search) work when CDN fails.
  NOTE: this is NOT the full Bootstrap implementation. Replace with the official
  `bootstrap.bundle.min.js` (v5.3.x) for complete functionality when possible.
*/
(function(window, document) {
  if (typeof window.bootstrap !== 'undefined') return;

  console.warn('Bootstrap fallback shim loaded — limited Modal support only. Replace with official bootstrap.bundle.js for full features.');

  function trigger(el, name) {
    try {
      el.dispatchEvent(new Event(name));
    } catch (e) {
      var ev = document.createEvent('Event');
      ev.initEvent(name, true, true);
      el.dispatchEvent(ev);
    }
  }

  class Modal {
    constructor(element) {
      this._element = (typeof element === 'string') ? document.querySelector(element) : element;
      this._isShown = false;
      this._backdrop = null;
    }

    show() {
      if (this._isShown) return;
      this._isShown = true;

      // create backdrop
      this._backdrop = document.createElement('div');
      this._backdrop.className = 'modal-backdrop fade show';
      document.body.appendChild(this._backdrop);

      // show modal element
      if (this._element) {
        this._element.classList.add('show');
        this._element.style.display = 'block';
        this._element.setAttribute('aria-modal', 'true');
        this._element.removeAttribute('aria-hidden');
      }

      trigger(this._element || document, 'shown.bs.modal');
    }

    hide() {
      if (!this._isShown) return;
      this._isShown = false;

      // remove backdrop
      if (this._backdrop && this._backdrop.parentNode) {
        this._backdrop.parentNode.removeChild(this._backdrop);
        this._backdrop = null;
      }

      if (this._element) {
        this._element.classList.remove('show');
        this._element.style.display = 'none';
        this._element.setAttribute('aria-hidden', 'true');
        this._element.removeAttribute('aria-modal');
      }

      trigger(this._element || document, 'hidden.bs.modal');
    }
  }

  // minimal collapse/dropdown stubs (no-op) to avoid console errors when invoked
  class Collapse {
    constructor() {}
    toggle() {}
  }
  class Dropdown {
    constructor() {}
    toggle() {}
  }

  window.bootstrap = {
    Modal: Modal,
    Collapse: Collapse,
    Dropdown: Dropdown
  };
})(window, document);
