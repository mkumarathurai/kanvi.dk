import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { Resvg } from '@resvg/resvg-js';
import { openSync } from 'fontkit';

// Rasterize the approved SVG with the website's Inter font. Keep the artwork
// unchanged; remove only excess transparent margins from the original viewBox.
const root = new URL('../', import.meta.url);
const svg = await readFile(new URL('public/brand/kanvi-logo.svg', root), 'utf8');
const font = openSync(fileURLToPath(new URL('resources/fonts/Inter.ttf', root)));
// Outline only the source's text nodes before rasterization: resvg otherwise
// uses the default weight of variable fonts instead of the requested 750.
const outlined = svg.replace(/<text\b([^>]*)>([^<]+)<\/text>/g, (_, attributes, text) => {
    const attrs = Object.fromEntries([...attributes.matchAll(/([\w-]+)="([^"]*)"/g)].map((match) => [match[1], match[2]]));
    const face = font.getVariation({ wght: Number(attrs['font-weight']) });
    const run = face.layout(text);
    const scale = Number(attrs['font-size']) / face.unitsPerEm;
    let x = Number(attrs.x);
    const y = Number(attrs.y);
    return run.glyphs.map((glyph, index) => {
        const position = run.positions[index];
        const path = `<path fill="${attrs.fill}" transform="translate(${x + position.xOffset * scale} ${y - position.yOffset * scale}) scale(${scale} ${-scale})" d="${glyph.path.toSVG()}"/>`;
        x += position.xAdvance * scale + Number(attrs['letter-spacing'] ?? 0);
        return path;
    }).join('');
});
const source = new Resvg(outlined);
const bounds = source.getBBox();
if (!bounds) throw new Error('Logo contains no visible artwork.');
// Preserve a small transparent inset so antialiased edges are never clipped.
bounds.x -= 2;
bounds.y -= 2;
bounds.width += 4;
bounds.height += 4;
source.cropByBBox(bounds);
const image = new Resvg(source.toString(), {
    fitTo: { mode: 'width', value: 360 },
}).render();
await mkdir(new URL('public/images/email/', root), { recursive: true });
await writeFile(new URL('public/images/email/kanvi-logo.png', root), image.asPng());
