/* Kalkulator wymiarów szyldu: pole z wpisanych wymiarów albo pomiar ze zdjęcia elewacji.
   Klient zaznacza element o znanej szerokości (np. drzwi) i miejsce na szyld,
   a skrypt przelicza piksele na centymetry i podaje pole w m².
   Wynik jest orientacyjny – działa, gdy zdjęcie zrobiono na wprost. */
(function () {
  "use strict";
  var nf = function (v, d) { return v.toLocaleString("pl-PL", { maximumFractionDigits: d, minimumFractionDigits: d }); };

  // pole szyldu z wpisanych wymiarów
  document.querySelectorAll("[data-dims]").forEach(function (box) {
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
  });

  document.querySelectorAll("[data-measure]").forEach(function (box) {
    var file = box.querySelector(".m-foto");
    var stage = box.querySelector(".measure-stage");
    var cv = box.querySelector("canvas");
    var ctx = cv.getContext("2d");
    var ref = box.querySelector(".m-ref");
    var out = box.querySelector(".measure-result");
    var modeBtns = box.querySelectorAll("[data-mode]");
    var clearBtn = box.querySelector("[data-clear]");
    var form = box.closest("form");
    var fw = form && form.querySelector(".d-w");
    var fh = form && form.querySelector(".d-h");
    var img = null, mode = "line", line = null, rect = null, dragging = false, autoText = "";

    // wpisuje wymiary do pól formularza (nie nadpisuje tego, co klient wpisał sam)
    function setDims(w, h) {
      if (!fw || !fh) return;
      if ((fw.value === "" || fw.value === autoText.split("x")[0]) && (fh.value === "" || fh.value === autoText.split("x")[1])) {
        fw.value = w; fh.value = h; autoText = w + "x" + h;
        fw.dispatchEvent(new Event("input", { bubbles: true }));
      }
    }

    function setMode(m) {
      mode = m;
      modeBtns.forEach(function (b) { b.setAttribute("aria-pressed", String(b.dataset.mode === m)); });
    }
    function len(l) { return l ? Math.hypot(l.b.x - l.a.x, l.b.y - l.a.y) : 0; }
    function pt(e) {
      var r = cv.getBoundingClientRect();
      return { x: (e.clientX - r.left) * cv.width / r.width, y: (e.clientY - r.top) * cv.height / r.height };
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
        if (len(line) > 8) label(ref.value ? ref.value + " cm" : "wpisz szerokość", (line.a.x + line.b.x) / 2, Math.min(line.a.y, line.b.y) - 28 * s, s);
      }
      if (rect) {
        var x = Math.min(rect.a.x, rect.b.x), y = Math.min(rect.a.y, rect.b.y);
        var w = Math.abs(rect.b.x - rect.a.x), h = Math.abs(rect.b.y - rect.a.y);
        ctx.fillStyle = "rgba(0,120,255,.25)"; ctx.fillRect(x, y, w, h);
        ctx.lineWidth = 3 * s; ctx.strokeStyle = "#0a6cff"; ctx.setLineDash([10 * s, 6 * s]); ctx.strokeRect(x, y, w, h); ctx.setLineDash([]);
        if (r && w > 4 && h > 4) label(Math.round(w * r) + " × " + Math.round(h * r) + " cm", x + w / 2, y + h + 6 * s, s);
      }
    }

    function ratio() {
      var cm = parseFloat(String(ref.value).replace(",", "."));
      return len(line) > 8 && cm > 0 ? cm / len(line) : 0;
    }

    function calc() {
      var r = ratio();
      if (!img) { out.textContent = ""; return; }
      if (len(line) <= 8) { out.textContent = "Krok 1: przeciągnij linię od lewej do prawej krawędzi drzwi (albo innego elementu, którego szerokość znasz)."; return; }
      if (!r) { out.textContent = "Wpisz szerokość zaznaczonego elementu w centymetrach."; return; }
      if (!rect || Math.abs(rect.b.x - rect.a.x) < 4 || Math.abs(rect.b.y - rect.a.y) < 4) { out.textContent = "Krok 2: przeciągnij prostokąt w miejscu, gdzie ma wisieć szyld."; return; }
      var w = Math.round(Math.abs(rect.b.x - rect.a.x) * r), h = Math.round(Math.abs(rect.b.y - rect.a.y) * r);
      var m2 = w * h / 10000;
      out.innerHTML = "Miejsce na szyld: <strong>ok. " + w + " × " + h + " cm</strong>, pole <strong>ok. " + nf(m2, 2) + " m²</strong>. Wymiary wpisaliśmy w pola powyżej.";
      setDims(w, h);
    }

    file.addEventListener("change", function () {
      var f = file.files && file.files[0];
      if (!f || !/^image\//.test(f.type)) { stage.hidden = true; img = null; return; }
      var im = new Image();
      im.onload = function () {
        var sc = Math.min(1, 1600 / Math.max(im.naturalWidth, im.naturalHeight));
        cv.width = Math.round(im.naturalWidth * sc); cv.height = Math.round(im.naturalHeight * sc);
        img = im; line = rect = null; setMode("line"); stage.hidden = false; draw(); calc();
      };
      im.onerror = function () { out.textContent = "Nie udało się otworzyć zdjęcia. Spróbuj pliku JPG lub PNG."; };
      im.src = URL.createObjectURL(f);
    });

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
    ref.addEventListener("input", function () { draw(); calc(); });
    modeBtns.forEach(function (b) { b.addEventListener("click", function () { setMode(b.dataset.mode); }); });
    clearBtn.addEventListener("click", function () {
      line = rect = null; setMode("line"); draw(); calc();
      if (fw && fh && autoText && fw.value + "x" + fh.value === autoText) { fw.value = fh.value = ""; fw.dispatchEvent(new Event("input", { bubbles: true })); }
      autoText = "";
    });
  });
})();
