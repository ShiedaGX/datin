/**
 * Pengelompokan titik peta (cluster) berbasis grid di koordinat layar, dipakai Peta Konektivitas dan
 * Peta Geospasial (D3). Titik yang berdekatan pada tingkat zoom saat ini digabung menjadi satu penanda
 * berangka; saat peta diperbesar kelompok terurai otomatis. Hanya titik yang tampak di layar yang diproses.
 *
 *   PetaCluster.group(items, xy, {k, tx, ty, w, h, cell, pinned, id})
 *     items  : array titik
 *     xy     : fungsi titik -> [x, y] pada sistem koordinat SVG sebelum zoom
 *     k,tx,ty: transform zoom D3 saat ini (layar = tx + k * x)
 *     w,h    : ukuran area peta (piksel)
 *     cell   : ukuran sel grid dalam piksel layar (bawaan 30)
 *     pinned : id titik yang harus selalu tampil sendiri (mis. titik terpilih)
 *     id     : fungsi titik -> id (bawaan p.id)
 *   -> { singles: [titik...], clusters: [{key, x, y, items}] }   (x, y = pusat kelompok, koordinat SVG)
 */
(function (root) {
    'use strict';

    function group(items, xy, o) {
        o = o || {};
        var k = o.k || 1, tx = o.tx || 0, ty = o.ty || 0;
        var w = o.w || 900, h = o.h || 600, cell = o.cell || 30, margin = 40;
        var idOf = o.id || function (p) { return p.id; };
        var cells = Object.create(null), order = [], singles = [];

        for (var i = 0; i < items.length; i++) {
            var p = items[i], c = xy(p);
            if (!c || !isFinite(c[0]) || !isFinite(c[1])) { continue; }
            var sx = tx + k * c[0], sy = ty + k * c[1];
            if (sx < -margin || sy < -margin || sx > w + margin || sy > h + margin) { continue; }   // di luar layar

            if (o.pinned != null && idOf(p) === o.pinned) { singles.push(p); continue; }

            var key = Math.floor(sx / cell) + ':' + Math.floor(sy / cell);
            var g = cells[key];
            if (!g) { g = cells[key] = { key: key, sx: 0, sy: 0, x: 0, y: 0, items: [] }; order.push(g); }
            g.items.push(p); g.x += c[0]; g.y += c[1];
        }

        var clusters = [];
        order.forEach(function (g) {
            if (g.items.length === 1) { singles.push(g.items[0]); return; }
            clusters.push({ key: g.key + '#' + g.items.length, x: g.x / g.items.length, y: g.y / g.items.length, items: g.items });
        });

        return { singles: singles, clusters: clusters };
    }

    /** Batas [x0, y0, x1, y1] semua titik kelompok (koordinat SVG). */
    function bounds(items, xy) {
        var x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
        items.forEach(function (p) {
            var c = xy(p);
            x0 = Math.min(x0, c[0]); x1 = Math.max(x1, c[0]);
            y0 = Math.min(y0, c[1]); y1 = Math.max(y1, c[1]);
        });
        return [x0, y0, x1, y1];
    }

    root.PetaCluster = { group: group, bounds: bounds };
})(typeof window !== 'undefined' ? window : globalThis);
