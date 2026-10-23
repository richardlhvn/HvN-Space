(() => {
  'use strict';
  const key = 'afterglow:saved:v1';
  const status = document.querySelector('#save-status');
  let timer;
  let storageAvailable = true;
  function readSaved() {
    try {
      const value = JSON.parse(localStorage.getItem(key) || '[]');
      return Array.isArray(value) ? [...new Set(value.filter(n => Number.isInteger(n) && n > 0))].slice(0, 100) : [];
    } catch { return []; }
  }
  let saved = readSaved();
  function announce(message) {
    clearTimeout(timer);
    status.textContent = message;
    status.classList.add('visible');
    timer = setTimeout(() => status.classList.remove('visible'), 3200);
  }
  function sync() {
    document.querySelectorAll('[data-save]').forEach(button => {
      const active = saved.includes(Number(button.dataset.save));
      button.setAttribute('aria-pressed', String(active));
      const name = button.dataset.name || button.getAttribute('aria-label')?.replace(/^(Save|Unsave) /, '') || 'this discovery';
      button.dataset.name = name;
      button.setAttribute('aria-label', `${active ? 'Unsave' : 'Save'} ${name}`);
    });
    document.querySelectorAll('.saved-count').forEach(el => el.textContent = saved.length);
  }
  document.addEventListener('click', event => {
    const button = event.target.closest('[data-save]');
    if (!button) return;
    const id = Number(button.dataset.save);
    if (!Number.isInteger(id) || id <= 0) return;
    const removing = saved.includes(id);
    if (!removing && saved.length >= 100) { announce('Your collection holds up to 100 discoveries.'); return; }
    const next = removing ? saved.filter(item => item !== id) : [...saved, id];
    try { localStorage.setItem(key, JSON.stringify(next)); saved = next; storageAvailable = true; }
    catch { storageAvailable = false; announce('Your browser couldn’t save this. Please allow local storage.'); }
    if (!storageAvailable) return;
    sync();
    announce(removing ? 'Removed from your saved discoveries' : 'A little discovery, saved for later');
    if (document.querySelector('#saved-items')) renderSaved();
  });
  window.addEventListener('storage', event => { if (event.key === key) { saved = readSaved(); sync(); if (document.querySelector('#saved-items')) renderSaved(); } });
  sync();

  const menu = document.querySelector('.mobile-menu');
  const nav = document.querySelector('#primary-nav');
  menu?.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    nav.classList.toggle('is-open', open);
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && menu?.getAttribute('aria-expanded') === 'true') { menu.click(); menu.focus(); } });

  const dialog = document.querySelector('#search-dialog');
  document.querySelectorAll('.search-trigger').forEach(button => button.addEventListener('click', () => { dialog.showModal(); document.querySelector('#global-search').focus(); }));
  document.querySelector('.search-close')?.addEventListener('click', () => dialog.close());
  dialog?.addEventListener('click', event => { if (event.target === dialog) { const box = dialog.getBoundingClientRect(); if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close(); } });

  function element(tag, className, text) {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (text) el.textContent = text;
    return el;
  }
  function savedCard(item) {
    const card = element('article', 'music-card');
    const art = element('div', 'card-art');
    const link = element('a');
    const url = new URL(item.url, location.origin);
    if (url.origin !== location.origin) return null;
    link.href = url.href;
    link.setAttribute('aria-label', `Explore ${item.title}`);
    const palette = ['lilac', 'rose', 'sage', 'sand', 'blue', 'peach'].includes(item.palette) ? item.palette : 'lilac';
    const image = element('div', `artwork palette-${palette}`);
    for (const name of ['art-sun', 'art-hill hill-one', 'art-hill hill-two', 'art-grain']) { const shape = element('span', name); shape.setAttribute('aria-hidden', 'true'); image.append(shape); }
    link.append(image);
    const save = element('button', 'save-button');
    save.type = 'button'; save.dataset.save = item.id; save.dataset.name = item.title;
    save.setAttribute('aria-label', `Unsave ${item.title}`);
    // Fixed icon markup, never user content.
    save.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>';
    art.append(link, save);
    const caption = element('div', 'card-caption');
    const text = element('div');
    const heading = element('h3');
    const title = element('a', '', item.title); title.href = url.href;
    heading.append(title); text.append(heading, element('p', '', item.subtitle || item.type)); caption.append(text);
    card.append(art, caption); return card;
  }
  let requestId = 0;
  async function renderSaved() {
    const target = document.querySelector('#saved-items');
    const empty = document.querySelector('#saved-empty');
    if (!target) return;
    const version = ++requestId;
    target.replaceChildren(); empty.hidden = saved.length > 0;
    if (!saved.length) return;
    target.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(`${afterglow.savedEndpoint}?ids=${saved.join(',')}`);
      if (!response.ok) throw new Error('Could not load discoveries');
      const items = await response.json();
      if (version !== requestId) return;
      if (!Array.isArray(items)) throw new Error('Invalid saved collection');
      for (const item of items) { const card = savedCard(item); if (card) target.append(card); }
      if (!items.length) { empty.hidden = false; }
      sync();
    } catch {
      if (version !== requestId) return;
      const error = element('p', 'editorial-notice', 'Your collection couldn’t load. Your saved items are still on this browser.');
      const retry = element('button', 'button button-dark', 'Try again'); retry.type = 'button'; retry.addEventListener('click', renderSaved); target.append(error, retry);
    } finally { if (version === requestId) target.removeAttribute('aria-busy'); }
  }
  renderSaved();
})();
