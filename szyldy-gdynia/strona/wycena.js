/* Szybka wycena: okno „Wyślij zdjęcie”, miniatury zdjęć, zmniejszanie zdjęć przed wysłaniem,
   pole szyldu z wymiarów i pomiar szyldu na zdjęciu elewacji. Bez bibliotek. */
(function () {
  "use strict";
  var nf = function (v, d) { return v.toLocaleString("pl-PL", { maximumFractionDigits: d, minimumFractionDigits: d }); };
  var isImg = function (f) { return /^image\/(jpeg|png|webp|heic|heif)/.test(f.type); };

  /* ---------- okno szybkiej wyceny ---------- */
  var sheet = document.getElementById("okno-wyceny");
  if (sheet && typeof sheet.showModal === "function") {
    document.addEventListener("click", function (e) {
      var a = e.target.closest("[data-quote-open], [data-rodzaj], a[href$='#formularz'], a[href='#wycena']");
      if (!a) return;
      var rodzaj = a.getAttribute("data-rodzaj");
      if (rodzaj) {
        document.querySelectorAll(".q-rodzaj").forEach(function (sel) { sel.value = rodzaj; });
        document.querySelectorAll(".q-more").forEach(function (d) { d.open = true; });
      }
      e.preventDefault();
      var inline = document.querySelector("form#formularz");
      if (inline && inline.closest(".quote-form")) inline = inline.closest(".quote-form");
      if (inline) {
        inline.scrollIntoView({ behavior: "smooth", block: "start" });
        var pick = inline.querySelector(".q-photos");
        if (pick) pick.focus({ preventScroll: true });
      } else {
        sheet.showModal();
      }
    });
    sheet.querySelector(".sheet-close").addEventListener("click", function () { sheet.close(); });
    sheet.addEventListener("click", function (e) { if (e.target === sheet) sheet.close(); });
  }

  document.querySelectorAll("[data-quote]").forEach(function (form) {
    var photos = form.querySelector(".q-photos");
    var thumbs = form.querySelector(".thumbs");
    var status = form.querySelector(".form-status");
    var send = form.querySelector(".btn-send");
    var measure = initMeasure(form);
    initDims(form);

    photos.addEventListener("change", function () {
      thumbs.innerHTML = "";
      var files = Array.prototype.slice.call(photos.files || []);
      files.forEach(function (f) {
        var li = document.createElement("li");
        if (isImg(f)) {
          var im = document.createElement("img");
          im.alt = ""; im.src = URL.createObjectURL(f);
          li.appendChild(im);
        } else {
          li.className = "file"; li.textContent = f.name;
        }
        thumbs.appendChild(li);
      });
      form.classList.toggle("has-photos", files.length > 0);
      form.querySelector(".photo-pick strong").textContent = files.length
        ? "Dodano: " + files.length + (files.length === 1 ? " plik" : files.length < 5 ? " pliki" : " plików") + " – zmień"
        : "Dodaj zdjęcie elewacji";
      var first = files.filter(isImg)[0];
      if (measure) measure.load(first || null);
    });

    function shrink(f) {
      return new Promise(function (res) {
        if (!isImg(f) || f.size < 1500000) return res(f);
        var im = new Image();
        im.onload = function () {
          var s = Math.min(1, 2000 / Math.max(im.naturalWidth, im.naturalHeight));
          var c = document.createElement("canvas");
          c.width = Math.round(im.naturalWidth * s); c.height = Math.round(im.naturalHeight * s);
          c.getContext("2d").drawImage(im, 0, 0, c.width, c.height);
          c.toBlob(function (b) {
            res(b ? new File([b], f.name.replace(/\.\w+$/, "") + ".jpg", { type: "image/jpeg" }) : f);
          }, "image/jpeg", 0.82);
        };
        im.onerror = function () { res(f); };
        im.src = URL.createObjectURL(f);
      });
    }

    function done(demo) {
      form.innerHTML = '<div class="form-done"><p class="form-done-title">Dziękujemy, zapytanie dotarło.</p>' +
        "<p>Odezwiemy się z wyceną na podany telefon lub e-mail.</p>" +
        (demo ? '<p class="form-note">To podgląd: formularz nie ma jeszcze podłączonej obsługi, więc nic nie zostało wysłane.</p>' : "") + "</div>";
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!form.reportValidity()) return;
      var action = form.getAttribute("action") || "";
      if (!action || action === "#" || /UZUPELNIJ/.test(action)) { done(true); return; }
      send.disabled = true; status.hidden = false; status.textContent = "Wysyłamy…";
      var fd = new FormData(form);
      fd.delete("zdjecia");
      Promise.all(Array.prototype.map.call(photos.files || [], shrink)).then(function (files) {
        files.forEach(function (f) { fd.append("zdjecia", f, f.name); });
        return fetch(action, { method: "POST", body: fd, headers: { Accept: "application/json" } });
      }).then(function (r) {
        if (!r.ok) throw new Error(r.status);
        done(false);
      }).catch(function () {
        send.disabled = false;
        status.textContent = "Nie udało się wysłać. Spróbuj jeszcze raz albo zadzwoń – numer jest na dole strony.";
      });
    });
  });

  /* ---------- filmy: bez ruchu dla osób z wyłączonymi animacjami ---------- */
  if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    document.querySelectorAll("video[autoplay]").forEach(function (v) { v.removeAttribute("autoplay"); v.pause(); });
  }

  /* ---------- mapa dzielnic ---------- */
  document.querySelectorAll(".map-wrap").forEach(function (wrap) {
    var pick = wrap.querySelector(".map-pick");
    function sel(name) {
      wrap.querySelectorAll("[data-d]").forEach(function (el) { el.classList.toggle("on", el.getAttribute("data-d") === name); });
      pick.innerHTML = "<strong>" + name + "</strong> – działamy tutaj. Wyślij zdjęcie elewacji, a przygotujemy wycenę.";
    }
    wrap.addEventListener("click", function (e) {
      var el = e.target.closest("[data-d]");
      if (el) sel(el.getAttribute("data-d"));
    });
    wrap.addEventListener("mouseover", function (e) {
      var el = e.target.closest(".m-chip");
      if (el) sel(el.getAttribute("data-d"));
    });
  });

  /* ---------- pole szyldu z wpisanych wymiarów ---------- */
  function initDims(form) {
    var box = form.querySelector("[data-dims]");
    if (!box) return;
    var fw = box.querySelector(".d-w"), fh = box.querySelector(".d-h");
    var out = box.querySelector(".dims-area"), hidden = box.querySelector("[name=pole_m2]");
    function num(i) { var v = parseFloat(String(i.value).replace(",", ".")); return v > 0 ? v : 0; }
    function upd() {
      var w = num(fw), h = num(fh);
      if (!w || !h) { out.textContent = "Pole szyldu: wpisz szerokość i wysokość."; hidden.value = ""; return; }
      var m2 = w * h / 10000;
      out.innerHTML = "Pole szyldu: <strong>" + nf(m2, 2) + " m²</strong> (" + nf(w, 0) + " × " + nf(h, 0) + " cm)";
      hidden.value = nf(m2, 2);
    }
    box.addEventListener("input", upd);
    upd();
  }

  /* ---------- pomiar szyldu na zdjęciu ---------- */
  function initMeasure(form) {
    var box = form.querySelector("[data-measure]");
    if (!box) return null;
    var empty = box.querySelector(".measure-empty");
    var stage = box.querySelector(".measure-stage");
    var cv = box.querySelector("canvas");
    var ctx = cv.getContext("2d");
    var ref = box.querySelector(".m-ref");
    var out = box.querySelector(".measure-result");
    var modeBtns = box.querySelectorAll("[data-mode]");
    var presets = box.querySelectorAll("[data-ref]");
    var fw = form.querySelector(".d-w"), fh = form.querySelector(".d-h");
    var img = null, mode = "line", line = null, rect = null, dragging = false, autoText = "";

    function setMode(m) {
      mode = m;
      modeBtns.forEach(function (b) { b.setAttribute("aria-pressed", String(b.dataset.mode === m)); });
    }
    function len(l) { return l ? Math.hypot(l.b.x - l.a.x, l.b.y - l.a.y) : 0; }
    function pt(e) {
      var r = cv.getBoundingClientRect();
      return { x: (e.clientX - r.left) * cv.width / r.width, y: (e.clientY - r.top) * cv.height / r.height };
    }
    function ratio() {
      var cm = parseFloat(String(ref.value).replace(",", "."));
      return len(line) > 8 && cm > 0 ? cm / len(line) : 0;
    }
    function label(text, x, y, s) {
      ctx.font = "700 " + Math.round(14 * s) + "px system-ui, sans-serif";
      var w = ctx.measureText(text).width + 12 * s, h = 22 * s;
      x = Math.max(0, Math.min(cv.width - w, x - w / 2));
      y = Math.max(0, Math.min(cv.height - h, y));
      ctx.fillStyle = "rgba(0,0,0,.78)"; ctx.fillRect(x, y, w, h);
      ctx.fillStyle = "#fff"; ctx.textBaseline = "middle"; ctx.fillText(text, x + 6 * s, y + h / 2);
    }
    function draw() {
      if (!img) return;
      var s = Math.max(1, cv.width / 600), r = ratio();
      ctx.drawImage(img, 0, 0, cv.width, cv.height);
      if (line) {
        ctx.lineWidth = 3 * s; ctx.strokeStyle = "#ffb800";
        ctx.beginPath(); ctx.moveTo(line.a.x, line.a.y); ctx.lineTo(line.b.x, line.b.y); ctx.stroke();
        [line.a, line.b].forEach(function (p) { ctx.beginPath(); ctx.arc(p.x, p.y, 5 * s, 0, 7); ctx.fillStyle = "#ffb800"; ctx.fill(); });
        if (len(line) > 8) label(ref.value ? ref.value + " cm" : "ile cm?", (line.a.x + line.b.x) / 2, Math.min(line.a.y, line.b.y) - 28 * s, s);
      }
      if (rect) {
        var x = Math.min(rect.a.x, rect.b.x), y = Math.min(rect.a.y, rect.b.y);
        var w = Math.abs(rect.b.x - rect.a.x), h = Math.abs(rect.b.y - rect.a.y);
        ctx.fillStyle = "rgba(0,120,255,.25)"; ctx.fillRect(x, y, w, h);
        ctx.lineWidth = 3 * s; ctx.strokeStyle = "#0a6cff"; ctx.setLineDash([10 * s, 6 * s]); ctx.strokeRect(x, y, w, h); ctx.setLineDash([]);
        if (r && w > 4 && h > 4) label(Math.round(w * r) + " × " + Math.round(h * r) + " cm", x + w / 2, y + h + 6 * s, s);
      }
    }
    function setDims(w, h) {
      if (!fw || !fh) return;
      var prev = autoText.split("x");
      if ((fw.value === "" || fw.value === prev[0]) && (fh.value === "" || fh.value === prev[1])) {
        fw.value = w; fh.value = h; autoText = w + "x" + h;
        fw.dispatchEvent(new Event("input", { bubbles: true }));
      }
    }
    function calc() {
      var r = ratio();
      if (!img) return;
      if (len(line) <= 8) { out.textContent = "Krok 1: przeciągnij linię od lewej do prawej krawędzi drzwi."; return; }
      if (!r) { out.textContent = "Podaj szerokość zaznaczonego elementu."; return; }
      if (!rect || Math.abs(rect.b.x - rect.a.x) < 4 || Math.abs(rect.b.y - rect.a.y) < 4) { out.textContent = "Krok 2: przeciągnij prostokąt w miejscu szyldu."; return; }
      var w = Math.round(Math.abs(rect.b.x - rect.a.x) * r), h = Math.round(Math.abs(rect.b.y - rect.a.y) * r);
      out.innerHTML = "Szyld: <strong>ok. " + w + " × " + h + " cm</strong>, pole <strong>ok. " + nf(w * h / 10000, 2) + " m²</strong>. Wpisaliśmy to w wymiary.";
      setDims(w, h);
    }

    cv.addEventListener("pointerdown", function (e) {
      if (!img) return;
      e.preventDefault(); cv.setPointerCapture(e.pointerId); dragging = true;
      var p = pt(e);
      if (mode === "line") line = { a: p, b: p }; else rect = { a: p, b: p };
      draw();
    });
    cv.addEventListener("pointermove", function (e) {
      if (!dragging) return;
      var p = pt(e);
      if (mode === "line") line.b = p; else rect.b = p;
      draw(); calc();
    });
    function end() {
      if (!dragging) return;
      dragging = false;
      if (mode === "line" && len(line) > 8) setMode("rect");
      draw(); calc();
    }
    cv.addEventListener("pointerup", end);
    cv.addEventListener("pointercancel", end);
    ref.addEventListener("input", function () {
      presets.forEach(function (b) { b.setAttribute("aria-pressed", String(b.dataset.ref === ref.value)); });
      draw(); calc();
    });
    presets.forEach(function (b) {
      b.addEventListener("click", function () { ref.value = b.dataset.ref; ref.dispatchEvent(new Event("input")); });
    });
    modeBtns.forEach(function (b) { b.addEventListener("click", function () { setMode(b.dataset.mode); }); });
    box.querySelector("[data-clear]").addEventListener("click", function () { line = rect = null; setMode("line"); draw(); calc(); });

    return {
      load: function (f) {
        if (!f) { img = null; stage.hidden = true; empty.hidden = false; return; }
        var im = new Image();
        im.onload = function () {
          var sc = Math.min(1, 1600 / Math.max(im.naturalWidth, im.naturalHeight));
          cv.width = Math.round(im.naturalWidth * sc); cv.height = Math.round(im.naturalHeight * sc);
          img = im; line = rect = null; setMode("line");
          stage.hidden = false; empty.hidden = true; draw(); calc();
        };
        im.src = URL.createObjectURL(f);
      }
    };
  }
})();
