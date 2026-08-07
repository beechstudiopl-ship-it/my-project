/**
 * Konfiguracja Eleventy.
 * Wejście: src/ — wynik: _site/ jako CZYSTY, statyczny HTML.
 * Bez frameworka JS. Strony miast i usług generują się z danych (src/_data).
 */
module.exports = function (eleventyConfig) {
  // Kopiujemy zasoby statyczne 1:1 do wyniku.
  eleventyConfig.addPassthroughCopy({ "src/assets": "assets" });
  eleventyConfig.addPassthroughCopy({ "src/css": "css" });

  // Rok do stopki itp. (deterministyczny — z ENV lub bieżący).
  eleventyConfig.addShortcode("year", () =>
    String(new Date().getFullYear())
  );

  // Filtr: numer telefonu -> format do atrybutu tel: (bez spacji i znaków).
  // Naprawia problem z analizy: zepsuty link `tel:+ 48 501...`.
  eleventyConfig.addFilter("telHref", (value) =>
    "tel:" + String(value || "").replace(/[^\d+]/g, "")
  );

  // Filtr: numer telefonu -> format do wa.me (same cyfry, bez plusa).
  eleventyConfig.addFilter("waNumber", (value) =>
    String(value || "").replace(/\D/g, "")
  );

  // Filtr: bezwzględny URL (baza z site.json + ścieżka).
  eleventyConfig.addFilter("absUrl", (path, base) => {
    const b = String(base || "").replace(/\/$/, "");
    const p = String(path || "");
    return b + (p.startsWith("/") ? p : "/" + p);
  });

  // Data ostatniej modyfikacji dla sitemap (stała w buildzie).
  eleventyConfig.addGlobalData("buildDate", () =>
    new Date().toISOString().slice(0, 10)
  );

  return {
    dir: {
      input: "src",
      includes: "_includes",
      data: "_data",
      output: "_site",
    },
    // Nunjucks dla szablonów i danych w plikach .html/.njk.
    htmlTemplateEngine: "njk",
    markdownTemplateEngine: "njk",
    templateFormats: ["njk", "md", "html"],
  };
};
