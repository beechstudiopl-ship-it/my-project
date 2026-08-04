/* Minimalny JS: menu mobilne + rok w stopce. Bez zależności. */
(function () {
  "use strict";
  var btn = document.querySelector("[data-menu-toggle]");
  var panel = document.querySelector("[data-menu]");
  if (btn && panel) {
    btn.addEventListener("click", function () {
      var open = panel.hasAttribute("data-open");
      if (open) { panel.removeAttribute("data-open"); btn.setAttribute("aria-expanded", "false"); }
      else { panel.setAttribute("data-open", ""); btn.setAttribute("aria-expanded", "true"); }
    });
    panel.addEventListener("click", function (e) {
      if (e.target.closest("a")) { panel.removeAttribute("data-open"); btn.setAttribute("aria-expanded", "false"); }
    });
  }
  document.querySelectorAll("[data-year]").forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });
})();
